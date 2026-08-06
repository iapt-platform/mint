#!/usr/bin/env python3
"""WikiPali 交互式登录——整个 Skill 里唯一接触密码的脚本。

密码只经 getpass 读入内存，不落盘、不进日志、不进对话上下文。
登录成功后只把 JWT 存进 ~/.wikipali/credentials.json（0600）。

用法：
    python3 wp_login.py                       # 登录当前默认站点
    python3 wp_login.py --api next            # 只为本次登录换站点
    python3 wp_login.py --username someone    # 免去输用户名一步

**必须在真正的终端里跑。** Claude Code 的 `!` 前缀没有交互式终端，
密码提示无处输入；模型也不该代跑此脚本。请另开一个 shell 执行。

确实要在自动化环境里登录时，用 --password-stdin 从管道读密码：

    read -rs PW && printf '%s' "$PW" | python3 wp_login.py --username me --password-stdin

注意别把密码写进命令行参数或直接敲进 Claude Code 的会话——argv 会进
ps / shell history，会话内容会进对话上下文，两者都留痕。
"""

import argparse
import getpass
import sys

import wp


def main(argv=None):
    parser = argparse.ArgumentParser(prog="wp_login.py", description="登录 WikiPali 并缓存用户 token")
    parser.add_argument("--api", help="本次登录使用的 API 地址（序号/简称/完整 url）")
    parser.add_argument("--username", help="用户名或邮箱；省略则交互输入")
    parser.add_argument(
        "--password-stdin", action="store_true",
        help="从 stdin 读密码（供自动化用；别让密码经过 argv 或对话）",
    )
    args = parser.parse_args(argv)

    try:
        client = wp.make_client(args)
    except wp.WpError as exc:
        print(f"错误：{exc}", file=sys.stderr)
        return 1

    print(f"登录站点：{client.api_note()}")
    if client.bucket_name != "online":
        print("注意：该站点的凭据与线上四站不通用。")

    interactive = sys.stdin.isatty()
    if not interactive and not args.password_stdin:
        # Claude Code 的 `!` 前缀、CI、管道都会走到这里：密码提示无处输入，
        # 直接说清楚该怎么办，不要让用户对着一个静默的提示符发愣
        print(
            "错误：当前不是交互式终端，无法安全地读取密码。\n"
            "  · 请另开一个真正的终端执行本脚本（Claude Code 的 `!` 前缀不行）；\n"
            "  · 或在自动化环境里用管道：... | python3 wp_login.py --username <名字> --password-stdin",
            file=sys.stderr,
        )
        return 1

    username = args.username
    if not username:
        if not interactive:
            print("错误：--password-stdin 模式必须同时给 --username。", file=sys.stderr)
            return 1
        username = input("用户名或邮箱：").strip()
    if not username:
        print("错误：用户名为空。", file=sys.stderr)
        return 1

    if args.password_stdin:
        password = sys.stdin.readline().rstrip("\n")
    else:
        try:
            password = getpass.getpass("密码（不会被保存）：")
        except (EOFError, KeyboardInterrupt):
            print("\n已取消。", file=sys.stderr)
            return 130
    if not password:
        print("错误：密码为空。", file=sys.stderr)
        return 1

    try:
        token = client.call("POST", "v2/sign-in", body={"username": username, "password": password})
    except wp.ApiError as exc:
        # sign-in 失败时服务端返回 400 + 'invalid token'，措辞会让人以为是 token 问题
        if exc.status in (400, 401):
            print("错误：用户名或密码不正确。", file=sys.stderr)
        else:
            print(f"错误：登录失败（HTTP {exc.status}）：{exc}", file=sys.stderr)
        return 1
    except wp.WpError as exc:
        print(f"错误：{exc}", file=sys.stderr)
        return 1
    finally:
        del password

    if not isinstance(token, str) or not token:
        print("错误：服务端没有返回 token。", file=sys.stderr)
        return 1

    try:
        current = client.call("GET", "v2/auth/current", token=token)
    except wp.WpError as exc:
        print(f"错误：token 拿到了但校验失败：{exc}", file=sys.stderr)
        return 1

    client.bucket["user"] = {
        "uid": current.get("id"),
        "username": current.get("realName"),
        "nickname": current.get("nickName"),
        "token": token,
        "logged_in_at": wp.iso_now(),
    }
    client.save()

    exp = wp.token_expiry(token)
    print(f"登录成功：{current.get('nickName')}（realName={current.get('realName')}，用作 studio_name）")
    print(f"token {wp.mask(token)} 到期 {wp.fmt_ts(exp)}，已写入 {wp.CREDS_PATH}（0600）")
    print("下一步：python3 wp.py ensure-model --name <模型标识>")
    return 0


if __name__ == "__main__":
    sys.exit(main())
