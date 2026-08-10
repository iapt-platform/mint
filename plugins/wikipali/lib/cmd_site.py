"""与站点和凭据状态有关的子命令：endpoint / whoami。"""

import sys
import time

from client import fmt_ts, make_client, mask, note, token_expiry
from creds import bucket_name_for, get_bucket, resolve_api_url, save_creds, load_creds, CREDS_PATH
from errors import ApiError, WpError, explain_api_error
from sites import SITES, expand_site_alias, normalize_api_url, site_label


def cmd_endpoint(args):
    creds = load_creds()
    current_url, source = resolve_api_url(getattr(args, "api", None), creds)

    if not args.target:
        for idx, site in enumerate(SITES, 1):
            mark = "  ← 当前" if site["url"] == current_url else ""
            print(f"  {idx}) {site['url']:<32} {site['version']} · {site['domain']}{mark}")
        if source in ("cli", "env"):
            src = "--api" if source == "cli" else "WIKIPALI_API_URL"
            note(f"注意：当前地址来自 {src}，是一次性覆盖，未写入凭据文件。")
        if current_url not in [s["url"] for s in SITES]:
            print(f"  *) {current_url:<32} 自定义地址  ← 当前")
        keys = "|".join(x["key"] for x in SITES)

        # 只有在真正的终端里才提示选择。agent 的 Bash 调用、CI、管道都没有 tty，
        # 那里必须保持纯展示——否则会卡在等输入上，且没人看得到提示符。
        if args.list or not sys.stdin.isatty():
            print(f"\n切换：wikipali endpoint <序号|{keys}|完整url>")
            return 0

        print(f"\n输入序号切换，直接回车不改（也可以 wikipali endpoint <{keys}|完整url>）")
        try:
            raw = input("选择：").strip()
        except (EOFError, KeyboardInterrupt):
            print()
            return 0
        if not raw:
            print("未改动。")
            return 0
        args.target = raw

    url = normalize_api_url(expand_site_alias(args.target))
    name = bucket_name_for(url)
    bucket = get_bucket(creds, name, url)
    bucket["api_url"] = url
    creds["current"] = name
    save_creds(creds)
    print(f"已切换到 {url}（{site_label(url)}）")
    if name != "online":
        note("提示：该地址是**另一个数据库**，与线上四站不共用数据与密钥。"
             "凭据和本地缓存都是独立的一桶，可能需要在该站点重新登录。")
    return 0


def cmd_whoami(args):
    client = make_client(args)
    print(f"API      : {client.api_note()}")
    print(f"凭据文件 : {CREDS_PATH}（桶：{client.bucket_name}）")

    user = client.bucket.get("user") or {}
    if user.get("token"):
        exp = token_expiry(user["token"])
        expired = exp is not None and exp < time.time()
        print(f"用户     : {user.get('username', '?')}  uid={user.get('uid', '?')}")
        print(f"           token {mask(user['token'])}  到期 {fmt_ts(exp)}{'  ⚠ 已过期' if expired else ''}")
    else:
        print("用户     : 未登录（wikipali-login）")

    model = client.bucket.get("model") or {}
    if model.get("token"):
        exp = token_expiry(model["token"])
        expired = exp is not None and exp < time.time()
        print(f"模型     : {model.get('name', '?')}  uid={model.get('uid', '?')}")
        print(f"           token {mask(model['token'])}  到期 {fmt_ts(exp)}{'  ⚠ 已过期' if expired else ''}")
    else:
        print("模型     : 未建立（wikipali ensure-model --name <模型名>）")

    tokens = client.bucket.get("access_tokens") or {}
    if tokens:
        print("access token：")
        for uid, item in tokens.items():
            exp = item.get("exp") or token_expiry(item.get("token", ""))
            expired = exp is not None and exp < time.time()
            book = item.get("book", 0)
            scope = "全部 book" if book == 0 else f"book {book}"
            name = item.get("channel_name") or ""
            print(f"  {uid[:8]}… {name:<24} {scope:<10} 到期 {fmt_ts(exp)}{'  ⚠ 已过期' if expired else ''}")
    else:
        print("access token：无（wikipali grant <channel>）")

    if args.check:
        try:
            data = client.call("GET", "v2/auth/current", token=client.user_token)
        except ApiError as exc:
            raise explain_api_error(exc, "校验用户 token")
        print(f"\n服务端确认：{data.get('nickName')} / realName={data.get('realName')}（studio_name 用它）")
    return 0
