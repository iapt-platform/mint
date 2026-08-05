#!/usr/bin/env python3
"""WikiPali 写入客户端。

以「AI 模型身份」把句子写入 WikiPali 句子库。子命令：

    endpoint     查看 / 切换 API 地址
    whoami       显示当前凭据状态
    ensure-model 幂等地建立模型记录并取模型身份 token
    revoke       撤销该模型已签出的全部 token
    channels     列出当前账号可编辑的 channel
    grant        为某个 channel 签发 access token 并缓存
    write        写入句子（分批 + 确认 + count 核对）

约束：只用 Python 标准库，目录自包含，可整体复制到任意项目。
密码只由 wp_login.py 接触，本脚本永不读密码。
"""

import argparse
import base64
import json
import os
import stat
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
from datetime import datetime, timezone

# ---------------------------------------------------------------------------
# 站点清单
# ---------------------------------------------------------------------------
# 四个线上地址共享同一个数据库和同一把 jwt 密钥，凭据完全通用；
# .org / .cc 是地区可达性，www / next 是代码版本（不是数据环境）。
# 开发机是另一个库、另一把密钥，故单独一桶，且永不作为自动 fallback 目标。

SITES = [
    {"key": "www", "url": "https://www.wikipali.org/api", "version": "稳定版", "domain": ".org"},
    {"key": "www.cc", "url": "https://www.wikipali.cc/api", "version": "稳定版", "domain": ".cc"},
    {"key": "next", "url": "https://next.wikipali.org/api", "version": "最新版", "domain": ".org"},
    {"key": "next.cc", "url": "https://next.wikipali.cc/api", "version": "最新版", "domain": ".cc"},
    {"key": "local", "url": "http://127.0.0.1:8000/api", "version": "开发机", "domain": "本机"},
]

ONLINE_URLS = [s["url"] for s in SITES if s["key"] != "local"]
LOCAL_URL = SITES[-1]["url"]
DEFAULT_API_URL = SITES[0]["url"]

CREDS_DIR = os.path.join(os.path.expanduser("~"), ".wikipali")
CREDS_PATH = os.path.join(CREDS_DIR, "credentials.json")

# access token 剩余不足这么多秒就重新签发，避免写到一半过期
TOKEN_REFRESH_MARGIN = 3600

DEFAULT_TIMEOUT = 30
WRITE_TIMEOUT = 120
DEFAULT_BATCH = 50


# ---------------------------------------------------------------------------
# 错误类型
# ---------------------------------------------------------------------------


class WpError(Exception):
    """面向用户的错误：main() 捕获后只打印 message，不打印堆栈。"""


class ApiError(WpError):
    def __init__(self, status, message, url=None, body=None):
        self.status = status
        self.url = url
        self.body = body
        super().__init__(message)


# ---------------------------------------------------------------------------
# 凭据文件
# ---------------------------------------------------------------------------


def load_creds():
    if not os.path.exists(CREDS_PATH):
        return {"current": "online"}
    try:
        with open(CREDS_PATH, "r", encoding="utf-8") as fh:
            data = json.load(fh)
    except (OSError, ValueError) as exc:
        raise WpError(f"凭据文件无法读取（{CREDS_PATH}）：{exc}")
    if not isinstance(data, dict):
        raise WpError(f"凭据文件格式不对（{CREDS_PATH}），应为 JSON 对象")
    data.setdefault("current", "online")
    return data


def save_creds(creds):
    os.makedirs(CREDS_DIR, mode=0o700, exist_ok=True)
    tmp = CREDS_PATH + ".tmp"
    flags = os.O_WRONLY | os.O_CREAT | os.O_TRUNC
    fd = os.open(tmp, flags, 0o600)
    try:
        with os.fdopen(fd, "w", encoding="utf-8") as fh:
            json.dump(creds, fh, ensure_ascii=False, indent=2)
            fh.write("\n")
    except Exception:
        os.unlink(tmp)
        raise
    os.replace(tmp, CREDS_PATH)
    os.chmod(CREDS_PATH, stat.S_IRUSR | stat.S_IWUSR)


def bucket_name_for(api_url):
    """凭据桶名。线上四地址共用 online 桶；开发机 local；其余地址自成一桶。"""
    if api_url in ONLINE_URLS:
        return "online"
    if api_url == LOCAL_URL:
        return "local"
    return "site:" + api_url


def get_bucket(creds, name, api_url=None):
    bucket = creds.setdefault(name, {})
    bucket.setdefault("api_url", api_url or (DEFAULT_API_URL if name == "online" else LOCAL_URL))
    bucket.setdefault("user", {})
    bucket.setdefault("model", {})
    bucket.setdefault("access_tokens", {})
    return bucket


def normalize_api_url(url):
    url = url.rstrip("/")
    parsed = urllib.parse.urlparse(url)
    if parsed.scheme not in ("http", "https"):
        raise WpError(f"API 地址必须以 http:// 或 https:// 开头：{url}")
    host = (parsed.hostname or "").lower()
    if parsed.scheme == "http" and host not in ("127.0.0.1", "localhost", "::1"):
        raise WpError(f"只有 127.0.0.1 / localhost 允许用 http://，其余必须 https://：{url}")
    return url


def resolve_api_url(cli_api, creds):
    """地址来源优先级：--api > 环境变量 > 凭据文件 > 内置默认。

    前两者是一次性覆盖，不写回凭据文件——否则「上周试了一次 next」会一直粘着。
    """
    if cli_api:
        return normalize_api_url(expand_site_alias(cli_api)), "cli"
    env = os.environ.get("WIKIPALI_API_URL")
    if env:
        return normalize_api_url(expand_site_alias(env)), "env"
    current = creds.get("current", "online")
    bucket = creds.get(current)
    if isinstance(bucket, dict) and bucket.get("api_url"):
        return normalize_api_url(bucket["api_url"]), "creds"
    return DEFAULT_API_URL, "default"


def expand_site_alias(value):
    """把序号 / 简称展开成完整 url；已是 url 则原样返回。"""
    value = value.strip()
    if value.isdigit():
        idx = int(value) - 1
        if 0 <= idx < len(SITES):
            return SITES[idx]["url"]
        raise WpError(f"站点序号超出范围：{value}（可选 1-{len(SITES)}）")
    for site in SITES:
        if value == site["key"]:
            return site["url"]
    if "://" in value:
        return value
    raise WpError(
        f"无法识别的站点：{value}。可用简称：" + " / ".join(s["key"] for s in SITES) + "，或直接给完整 url"
    )


def site_label(api_url):
    for site in SITES:
        if site["url"] == api_url:
            return f"{site['version']} · {site['domain']}"
    return "自定义地址"


# ---------------------------------------------------------------------------
# HTTP
# ---------------------------------------------------------------------------


def mask(token):
    if not token:
        return "(无)"
    if len(token) <= 16:
        return token[:4] + "…"
    return token[:8] + "…" + token[-4:]


def jwt_payload(token):
    """不验签地读出 JWT payload，仅用于显示有效期。"""
    try:
        part = token.split(".")[1]
        part += "=" * (-len(part) % 4)
        return json.loads(base64.urlsafe_b64decode(part.encode("ascii")))
    except Exception:
        return {}


def token_expiry(token):
    exp = jwt_payload(token).get("exp")
    return int(exp) if isinstance(exp, (int, float)) else None


def fmt_ts(ts):
    if not ts:
        return "未知"
    return datetime.fromtimestamp(ts, tz=timezone.utc).astimezone().strftime("%Y-%m-%d %H:%M")


def note(msg):
    print(msg, file=sys.stderr)


def http_json(api_url, method, path, token=None, body=None, query=None, timeout=DEFAULT_TIMEOUT):
    """发一个 JSON 请求，返回解析后的响应体（dict）。

    网络层失败抛 urllib 的异常（由 Client 决定是否 fallback）；
    HTTP 层失败抛 ApiError，带上服务端 message。
    """
    url = api_url + "/" + path.lstrip("/")
    if query:
        url += "?" + urllib.parse.urlencode({k: v for k, v in query.items() if v is not None})
    data = None
    headers = {"Accept": "application/json", "User-Agent": "wikipali-write-skill"}
    if body is not None:
        data = json.dumps(body, ensure_ascii=False).encode("utf-8")
        headers["Content-Type"] = "application/json"
    if token:
        headers["Authorization"] = "Bearer " + token
    req = urllib.request.Request(url, data=data, headers=headers, method=method)
    try:
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            raw = resp.read().decode("utf-8", "replace")
            status = resp.status
    except urllib.error.HTTPError as exc:
        raw = exc.read().decode("utf-8", "replace")
        status = exc.code
        payload = safe_json(raw)
        message = payload.get("message") if isinstance(payload, dict) else None
        raise ApiError(status, message or f"HTTP {status}", url=url, body=payload or raw)
    payload = safe_json(raw)
    if not isinstance(payload, dict):
        raise ApiError(status, f"响应不是 JSON：{raw[:200]}", url=url, body=raw)
    if not payload.get("ok", False):
        raise ApiError(status, payload.get("message") or "请求失败", url=url, body=payload)
    return payload.get("data")


def safe_json(raw):
    try:
        return json.loads(raw)
    except ValueError:
        return None


class Client:
    """按站点收发请求，并在线上地址之间做出声的 fallback。"""

    def __init__(self, api_url, source, creds, allow_fallback=True):
        self.api_url = api_url
        self.source = source
        self.creds = creds
        self.bucket_name = bucket_name_for(api_url)
        self.bucket = get_bucket(creds, self.bucket_name, api_url)
        self.allow_fallback = allow_fallback and api_url in ONLINE_URLS

    # -- 凭据 ---------------------------------------------------------------

    @property
    def user_token(self):
        token = (self.bucket.get("user") or {}).get("token")
        if not token:
            raise WpError(
                "尚未登录。请自己执行（Claude Code 里用 ! 前缀）：\n"
                "    ! python3 " + os.path.join(os.path.dirname(os.path.abspath(__file__)), "wp_login.py")
            )
        return token

    @property
    def model(self):
        model = self.bucket.get("model") or {}
        if not model.get("token"):
            raise WpError("尚未取得模型身份 token。请先跑：python3 wp.py ensure-model --name <模型名>")
        return model

    def save(self):
        save_creds(self.creds)

    # -- 请求 ---------------------------------------------------------------

    def fallback_order(self):
        """同版本的另一域名 → 另一版本的同域名 → 其余。绝不含 local。"""
        cur = next((s for s in SITES if s["url"] == self.api_url), None)
        if not cur:
            return []
        others = [s for s in SITES if s["key"] != "local" and s["url"] != self.api_url]
        others.sort(
            key=lambda s: (
                0 if s["version"] == cur["version"] else 1,
                0 if s["domain"] == cur["domain"] else 1,
            )
        )
        return [s["url"] for s in others]

    def call(self, method, path, token=None, body=None, query=None, timeout=DEFAULT_TIMEOUT):
        urls = [self.api_url] + (self.fallback_order() if self.allow_fallback else [])
        last = None
        for idx, url in enumerate(urls):
            try:
                data = http_json(url, method, path, token=token, body=body, query=query, timeout=timeout)
            except (urllib.error.URLError, TimeoutError, OSError) as exc:
                # 仅网络层不可达才换站点；HTTP 错误是服务端的明确答复，不该被掩盖
                last = exc
                reason = getattr(exc, "reason", exc)
                if idx + 1 < len(urls):
                    note(f"⚠ {url} 连接失败（{reason}），改用 {urls[idx + 1]}")
                continue
            if url != self.api_url:
                # fallback 成功后本次会话都用它，但不写回凭据文件
                note(f"⚠ 本次请求实际发往 {url}（{site_label(url)}）")
                self.api_url = url
            return data
        raise WpError(f"所有可用站点都连不上，最后一次错误：{last}")

    def api_note(self):
        src = {"cli": "--api", "env": "环境变量", "creds": "凭据文件", "default": "内置默认"}[self.source]
        return f"{self.api_url}（{site_label(self.api_url)}，来源：{src}）"


def make_client(args, allow_fallback=True):
    creds = load_creds()
    api_url, source = resolve_api_url(getattr(args, "api", None), creds)
    return Client(api_url, source, creds, allow_fallback=allow_fallback)


def explain_api_error(exc, what):
    """把 HTTP 状态翻译成对操作者有意义的话（见 references/api.md 的错误约定）。"""
    if exc.status == 401:
        return WpError(
            f"{what}：401 凭据失效或已被撤销。\n"
            "  · 用户 token 失效 → 重新登录：! python3 scripts/wp_login.py\n"
            "  · 模型 token 失效或被撤销 → 重跑：python3 wp.py ensure-model\n"
            "  不要自动重试。"
        )
    if exc.status == 403:
        return WpError(f"{what}：403 无权限（不是 channel 的 owner/协作者，或不是模型 owner 本人）。")
    if exc.status == 404:
        return WpError(
            f"{what}：404。若这是较新的端点，可能是当前站点跑的是稳定版代码、端点尚未上线；\n"
            "  可切到最新版试试：python3 wp.py endpoint next\n"
            "  否则才是资源真的不存在。"
        )
    if exc.status == 409:
        return WpError(f"{what}：409 同名记录已存在。")
    if exc.status == 422:
        return WpError(f"{what}：422 参数校验失败——{exc}")
    return WpError(f"{what}：HTTP {exc.status} {exc}")


# ---------------------------------------------------------------------------
# 子命令：endpoint
# ---------------------------------------------------------------------------


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
        print("\n切换：python3 wp.py endpoint <序号|www|www.cc|next|next.cc|local|完整url>")
        return 0

    url = normalize_api_url(expand_site_alias(args.target))
    name = bucket_name_for(url)
    bucket = get_bucket(creds, name, url)
    bucket["api_url"] = url
    creds["current"] = name
    save_creds(creds)
    print(f"已切换到 {url}（{site_label(url)}）")
    if name != "online":
        note("提示：该地址与线上四站不共用数据库/密钥，凭据是独立的一桶，可能需要重新登录。")
    return 0


# ---------------------------------------------------------------------------
# 子命令：whoami
# ---------------------------------------------------------------------------


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
        print("用户     : 未登录（! python3 scripts/wp_login.py）")

    model = client.bucket.get("model") or {}
    if model.get("token"):
        exp = token_expiry(model["token"])
        expired = exp is not None and exp < time.time()
        print(f"模型     : {model.get('name', '?')}  uid={model.get('uid', '?')}")
        print(f"           token {mask(model['token'])}  到期 {fmt_ts(exp)}{'  ⚠ 已过期' if expired else ''}")
    else:
        print("模型     : 未建立（python3 wp.py ensure-model --name <模型名>）")

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
        print("access token：无（python3 wp.py grant <channel>）")

    if args.check:
        try:
            data = client.call("GET", "v2/auth/current", token=client.user_token)
        except ApiError as exc:
            raise explain_api_error(exc, "校验用户 token")
        print(f"\n服务端确认：{data.get('nickName')} / realName={data.get('realName')}（studio_name 用它）")
    return 0


# ---------------------------------------------------------------------------
# 子命令：ensure-model
# ---------------------------------------------------------------------------


def cmd_ensure_model(args):
    client = make_client(args)
    token = client.user_token

    name = args.name or (client.bucket.get("model") or {}).get("name") or os.environ.get("WIKIPALI_MODEL_NAME")
    if not name:
        raise WpError(
            "必须指定模型名：--name <模型标识>（如 claude-opus-5）。\n"
            "该名字会成为句子的作者署名，不要用别的模型的名字。"
        )

    try:
        current = client.call("GET", "v2/auth/current", token=token)
    except ApiError as exc:
        raise explain_api_error(exc, "取当前用户信息")
    studio_name = current.get("realName")
    if not studio_name:
        raise WpError("服务端没有返回 realName，无法确定 studio_name。")

    # 1) 按 studio + keyword 查，keyword 是模糊匹配，客户端自己做精确比对
    try:
        listed = client.call(
            "GET", "v2/ai-model", token=token,
            query={"view": "studio", "name": studio_name, "keyword": name},
        )
    except ApiError as exc:
        raise explain_api_error(exc, "查询模型列表")
    rows = (listed or {}).get("rows") or []
    found = next((r for r in rows if r.get("name") == name), None)

    if found:
        print(f"已存在模型记录：{name}  uid={found['uid']}")
    else:
        body = {"name": name, "studio_name": studio_name, "privacy": args.privacy}
        for field, value in (("model", args.model), ("url", args.url), ("description", args.description)):
            if value is not None:
                body[field] = value
        try:
            found = client.call("POST", "v2/ai-model", token=token, body=body)
            print(f"已创建模型记录：{name}  uid={found['uid']}")
        except ApiError as exc:
            if exc.status != 409:
                raise explain_api_error(exc, "创建模型记录")
            # 并发或模糊匹配漏网：重查一次
            listed = client.call(
                "GET", "v2/ai-model", token=token,
                query={"view": "studio", "name": studio_name, "keyword": name},
            )
            rows = (listed or {}).get("rows") or []
            found = next((r for r in rows if r.get("name") == name), None)
            if not found:
                raise WpError(f"服务端说 {name} 已存在（409），但列表里查不到，无法继续。")
            print(f"已存在模型记录：{name}  uid={found['uid']}")

    # 2) 增量补字段（update 是增量的，未提交的字段保持原值）
    patch = {}
    for field, value in (("model", args.model), ("url", args.url), ("description", args.description)):
        if value is not None and found.get(field) != value:
            patch[field] = value
    if args.privacy and found.get("privacy") != args.privacy:
        patch["privacy"] = args.privacy
    if patch:
        try:
            found = client.call("PUT", f"v2/ai-model/{found['uid']}", token=token, body=patch)
            print(f"已更新字段：{', '.join(patch)}")
        except ApiError as exc:
            raise explain_api_error(exc, "更新模型记录")

    # 3) 取模型身份 token
    try:
        issued = client.call("GET", f"v2/ai-model-token/{found['uid']}", token=token)
    except ApiError as exc:
        raise explain_api_error(exc, "签发模型身份 token")

    client.bucket["model"] = {
        "uid": issued["uid"],
        "name": issued["name"],
        "token": issued["token"],
        "issued_at": iso_now(),
    }
    client.save()
    exp = token_expiry(issued["token"])
    print(f"模型身份 token 已缓存：{mask(issued['token'])}  到期 {fmt_ts(exp)}")
    print(f"写入的句子将署名为该模型（editor_uid={issued['uid']}）。")
    return 0


def cmd_revoke(args):
    client = make_client(args)
    model = client.bucket.get("model") or {}
    uid = args.uid or model.get("uid")
    if not uid:
        raise WpError("没有可撤销的模型：请给 --uid <模型 uid>，或先跑 ensure-model。")
    if not args.yes and not confirm(f"将撤销模型 {model.get('name', uid)} 已签出的全部 token，继续？"):
        print("已取消。")
        return 1
    try:
        data = client.call("DELETE", f"v2/ai-model-token/{uid}", token=client.user_token)
    except ApiError as exc:
        raise explain_api_error(exc, "撤销模型 token")
    if model.get("uid") == uid:
        client.bucket["model"] = {"uid": uid, "name": model.get("name")}
        client.save()
    print(f"已撤销 {data.get('name')} 的全部 token（token_version={data.get('token_version')}）。")
    print("本地缓存的模型 token 已清除，需要写入时请重跑 ensure-model。")
    return 0


# ---------------------------------------------------------------------------
# 子命令：channels
# ---------------------------------------------------------------------------


def fetch_channels(client, search=None):
    try:
        data = client.call(
            "GET", "v2/channel", token=client.user_token,
            query={"view": "user-edit", "order": "updated_at", "dir": "desc", "limit": 200, "search": search},
        )
    except ApiError as exc:
        raise explain_api_error(exc, "获取可编辑 channel 列表")
    return (data or {}).get("rows") or []


def cmd_channels(args):
    client = make_client(args)
    rows = fetch_channels(client, args.search)
    if args.json:
        print(json.dumps(rows, ensure_ascii=False, indent=2))
        return 0
    if not rows:
        print("当前账号没有任何可编辑的 channel。")
        return 1
    print(f"可编辑 channel（{len(rows)} 个，按更新时间倒序）：")
    for idx, ch in enumerate(rows, 1):
        print(
            f"  {idx:>2}) {ch.get('name', '')[:32]:<34} {str(ch.get('lang', '')):<6} "
            f"{ch.get('uid', '')[:8]}…  {ch.get('role', '')}"
        )
    return 0


def pick_channel(client, given, interactive=True):
    """返回 (uid, name)。given 可以是 uid、序号或名字片段；为空则交互选择。"""
    rows = fetch_channels(client)
    if not rows:
        raise WpError("当前账号没有任何可编辑的 channel，无法继续。")

    if given:
        for ch in rows:
            if ch.get("uid") == given:
                return ch["uid"], ch.get("name")
        if given.isdigit() and 1 <= int(given) <= len(rows):
            ch = rows[int(given) - 1]
            return ch["uid"], ch.get("name")
        matched = [c for c in rows if given.lower() in (c.get("name") or "").lower()]
        if len(matched) == 1:
            return matched[0]["uid"], matched[0].get("name")
        if len(matched) > 1:
            names = ", ".join(c.get("name", "") for c in matched[:5])
            raise WpError(f"「{given}」匹配到多个 channel：{names}…… 请给完整 uid。")
        # 不在可编辑列表里的 uid：直接用，但回显不出名字
        if len(given) >= 32:
            note(f"⚠ {given} 不在可编辑列表中，仍按 uid 使用——签发 access token 时可能返回 count: 0。")
            return given, None
        raise WpError(f"找不到 channel：{given}")

    if not (interactive and sys.stdin.isatty()):
        raise WpError("未指定 channel，且当前不是交互式终端。请先跑 `wp.py channels` 再用 --channel 指定。")

    print("可编辑 channel：")
    for idx, ch in enumerate(rows, 1):
        print(f"  {idx:>2}) {ch.get('name', '')[:32]:<34} {str(ch.get('lang', '')):<6} {ch.get('uid', '')[:8]}…")
    raw = input("选择序号：").strip()
    if not raw.isdigit() or not (1 <= int(raw) <= len(rows)):
        raise WpError("输入无效。")
    ch = rows[int(raw) - 1]
    return ch["uid"], ch.get("name")


# ---------------------------------------------------------------------------
# 子命令：grant
# ---------------------------------------------------------------------------


def cached_access_token(client, channel_uid, book):
    item = (client.bucket.get("access_tokens") or {}).get(channel_uid)
    if not item or not item.get("token"):
        return None
    # book 0 是「不限 book」，能覆盖任何请求；否则必须完全一致
    if item.get("book", 0) != 0 and item.get("book") != book:
        return None
    exp = item.get("exp") or token_expiry(item["token"])
    if exp and exp - time.time() < TOKEN_REFRESH_MARGIN:
        return None
    return item


def grant_access_token(client, channel_uid, channel_name, book, force=False):
    if not force:
        cached = cached_access_token(client, channel_uid, book)
        if cached:
            return cached

    # book 必须是整数：服务端用 !== 严格比较，"1" !== 1 恒真会导致鉴权失败
    payload = [{"res_type": "channel", "res_id": channel_uid, "power": "edit", "book": int(book)}]
    try:
        data = client.call("POST", "v2/access-token", token=client.user_token, body={"payload": payload})
    except ApiError as exc:
        raise explain_api_error(exc, "签发 access token")
    rows = (data or {}).get("rows") or []
    if not rows:
        # 无权时服务端静默跳过该条，rows 为空——等同 403，绝不能继续写
        raise WpError(
            f"签发 access token 返回 count: 0，说明当前账号对 channel {channel_uid} 没有编辑权。\n"
            "不要继续写入。请确认选对了 channel，或让 owner 授予 ≥ editor 权限。"
        )
    row = rows[0]
    item = {
        "token": row["token"],
        "book": int(book),
        "exp": (row.get("payload") or {}).get("exp"),
        "granted_at": iso_now(),
    }
    if channel_name:
        item["channel_name"] = channel_name
    client.bucket.setdefault("access_tokens", {})[channel_uid] = item
    client.save()
    return item


def cmd_grant(args):
    client = make_client(args)
    uid, name = pick_channel(client, args.channel)
    item = grant_access_token(client, uid, name, args.book, force=args.force)
    scope = "全部 book" if item["book"] == 0 else f"book {item['book']}"
    print(f"channel : {name or '(未知)'}  {uid}")
    print(f"范围    : {scope}")
    print(f"token   : {mask(item['token'])}  到期 {fmt_ts(item.get('exp'))}")
    return 0


# ---------------------------------------------------------------------------
# 子命令：write
# ---------------------------------------------------------------------------

SENT_REQUIRED = ("book_id", "paragraph", "word_start", "word_end", "content")


def load_sentences(args):
    if args.file == "-":
        raw = sys.stdin.read()
    else:
        try:
            with open(args.file, "r", encoding="utf-8") as fh:
                raw = fh.read()
        except OSError as exc:
            raise WpError(f"读不了输入文件：{exc}")
    try:
        data = json.loads(raw)
    except ValueError as exc:
        raise WpError(f"输入不是合法 JSON：{exc}")

    default_channel = None
    if isinstance(data, dict):
        default_channel = data.get("channel_uid") or data.get("channel")
        data = data.get("sentences")
    if not isinstance(data, list) or not data:
        raise WpError('输入必须是句子数组，或 {"channel_uid": ..., "sentences": [...]}，且非空。')
    return data, default_channel


def normalize_sentences(rows, channel_uid, default_content_type):
    out = []
    for idx, row in enumerate(rows):
        if not isinstance(row, dict):
            raise WpError(f"第 {idx + 1} 条不是对象。")
        missing = [f for f in SENT_REQUIRED if row.get(f) is None]
        if missing:
            raise WpError(f"第 {idx + 1} 条缺字段：{', '.join(missing)}")
        try:
            sent = {
                "book_id": int(row["book_id"]),
                "paragraph": int(row["paragraph"]),
                "word_start": int(row["word_start"]),
                "word_end": int(row["word_end"]),
                "content": str(row["content"]),
                "content_type": row.get("content_type") or default_content_type,
                "channel_uid": row.get("channel_uid") or channel_uid,
            }
        except (TypeError, ValueError) as exc:
            raise WpError(f"第 {idx + 1} 条字段类型不对：{exc}")
        if not sent["channel_uid"]:
            raise WpError(f"第 {idx + 1} 条没有 channel_uid，且未通过 --channel 指定。")
        out.append(sent)
    return out


def sent_key(sent):
    return (
        int(sent["book_id"]),
        int(sent["paragraph"]),
        int(sent["word_start"]),
        int(sent["word_end"]),
        sent["channel_uid"],
    )


def row_key(row):
    channel = row.get("channel") or {}
    return (
        int(row.get("book", -1)),
        int(row.get("paragraph", -1)),
        int(row.get("word_start", -1)),
        int(row.get("word_end", -1)),
        channel.get("uid"),
    )


def confirm(question):
    if not sys.stdin.isatty():
        return False
    answer = input(f"{question} [y/N] ").strip().lower()
    return answer in ("y", "yes")


def iso_now():
    return datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")


def cmd_write(args):
    client = make_client(args)
    # 先确认凭据齐备再解析输入：缺 token 时不该让用户看半张回显
    client.user_token
    model = client.model
    rows, file_channel = load_sentences(args)

    channel_hint = args.channel or file_channel
    uid = name = None
    if channel_hint or not any(r.get("channel_uid") for r in rows if isinstance(r, dict)):
        uid, name = pick_channel(client, channel_hint)
    sentences = normalize_sentences(rows, uid, args.content_type)

    channels = sorted({s["channel_uid"] for s in sentences})
    books = sorted({s["book_id"] for s in sentences})
    names = {uid: name} if uid else {}
    for cuid in channels:
        if cuid not in names:
            names[cuid] = channel_display_name(client, cuid)

    # 写入前的确认：出问题时必须知道是哪一版代码、写进了哪个 channel
    print("=" * 72)
    print(f"API      : {client.api_note()}")
    for cuid in channels:
        print(f"channel  : {names.get(cuid) or '(未知)'}  {cuid}")
    print(f"book     : {', '.join(str(b) for b in books)}")
    print(f"模型身份 : {model.get('name')}  uid={model.get('uid')}")
    print(f"句子数   : {len(sentences)}（每批 {args.batch}）")
    print("-" * 72)
    for sent in sentences[: args.preview]:
        summary = sent["content"].replace("\n", " ")
        if len(summary) > 50:
            summary = summary[:50] + "…"
        print(f"  {sent['book_id']}-{sent['paragraph']}-{sent['word_start']}-{sent['word_end']}  {summary}")
    if len(sentences) > args.preview:
        print(f"  …… 其余 {len(sentences) - args.preview} 条")
    print("-" * 72)
    print("⚠ 相同位置（book/paragraph/word_start/word_end/channel）的已有句子将被覆盖。")
    print("=" * 72)

    if args.dry_run:
        print("--dry-run：未发送任何请求。")
        return 0
    if not args.yes and not confirm("确认写入？"):
        print("已取消，未写入任何内容。")
        return 1

    # 每个 channel 一张 access token（缓存命中就不重签）
    tokens = {}
    for cuid in channels:
        book_scope = 0 if len(books) > 1 else books[0]
        if args.book is not None:
            book_scope = args.book
        item = grant_access_token(client, cuid, names.get(cuid), book_scope)
        tokens[cuid] = item["token"]

    written = {}
    failed = []
    model_token = model["token"]
    for start in range(0, len(sentences), args.batch):
        batch = sentences[start : start + args.batch]
        body = {
            "sentences": [
                {
                    "book_id": s["book_id"],
                    "paragraph": s["paragraph"],
                    "word_start": s["word_start"],
                    "word_end": s["word_end"],
                    "channel_uid": s["channel_uid"],
                    "content": s["content"],
                    "content_type": s["content_type"],
                    "access_token": tokens[s["channel_uid"]],
                }
                for s in batch
            ]
        }
        try:
            data = client.call("POST", "v2/sentence", token=model_token, body=body, timeout=WRITE_TIMEOUT)
        except ApiError as exc:
            if exc.status != 401:
                raise explain_api_error(exc, "写入句子")
            # 模型 token 过期或被撤销：重取一次再试，仍失败才提示重新登录
            model_token = refresh_model_token(client)
            try:
                data = client.call("POST", "v2/sentence", token=model_token, body=body, timeout=WRITE_TIMEOUT)
            except ApiError as retry_exc:
                raise explain_api_error(retry_exc, "写入句子（已重签模型 token 后重试）")
        returned = (data or {}).get("rows") or []
        for row in returned:
            written[row_key(row)] = row
        got = len(returned)
        print(f"批次 {start // args.batch + 1}: 提交 {len(batch)}，服务端确认 {got}")
        if got < len(batch):
            # HTTP 200 不等于全部写入：逐句鉴权失败是静默 continue 掉的
            for s in batch:
                if sent_key(s) not in written:
                    failed.append(s)

    print("-" * 72)
    print(f"合计提交 {len(sentences)} 条，确认写入 {len(written)} 条。")
    sample = next(iter(written.values()), None)
    if sample:
        editor = (sample.get("editor") or {}).get("nickName") or (sample.get("editor") or {}).get("name")
        print(f"署名核对：第一条的 editor = {editor}")
    if failed:
        print(f"⚠ 有 {len(failed)} 条未写入（服务端逐句鉴权失败会静默跳过）：")
        for s in failed[:10]:
            print(f"  {s['book_id']}-{s['paragraph']}-{s['word_start']}-{s['word_end']}  channel={s['channel_uid'][:8]}…")
        if len(failed) > 10:
            print(f"  …… 其余 {len(failed) - 10} 条")
        return 1
    return 0


def channel_display_name(client, uid):
    try:
        data = client.call("GET", f"v2/channel/{uid}", token=client.user_token)
    except (ApiError, WpError):
        return None
    if isinstance(data, dict):
        return data.get("name")
    return None


def refresh_model_token(client):
    note("⚠ 模型 token 被拒（过期或已撤销），正在重新签发……")
    model = client.bucket.get("model") or {}
    if not model.get("uid"):
        raise WpError("缓存里没有模型 uid，无法重签。请跑：python3 wp.py ensure-model --name <模型名>")
    try:
        issued = client.call("GET", f"v2/ai-model-token/{model['uid']}", token=client.user_token)
    except ApiError as exc:
        raise explain_api_error(exc, "重新签发模型 token")
    model.update({"uid": issued["uid"], "name": issued["name"], "token": issued["token"], "issued_at": iso_now()})
    client.bucket["model"] = model
    client.save()
    return issued["token"]


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------


def build_parser():
    parser = argparse.ArgumentParser(
        prog="wp.py",
        description="WikiPali 写入客户端（以 AI 模型身份写入句子）",
        formatter_class=argparse.RawDescriptionHelpFormatter,
    )
    parser.add_argument("--api", help="本次调用使用的 API 地址（序号/简称/完整 url），不写回凭据文件")
    sub = parser.add_subparsers(dest="command", required=True)

    p = sub.add_parser("endpoint", help="查看 / 切换 API 地址")
    p.add_argument("target", nargs="?", help="序号、简称（www/www.cc/next/next.cc/local）或完整 url")
    p.set_defaults(func=cmd_endpoint)

    p = sub.add_parser("whoami", help="显示当前凭据状态")
    p.add_argument("--check", action="store_true", help="额外向服务端校验用户 token")
    p.set_defaults(func=cmd_whoami)

    p = sub.add_parser("ensure-model", help="幂等地建立模型记录并取模型身份 token")
    p.add_argument("--name", help="模型标识，如 claude-opus-5（会成为句子作者署名）")
    p.add_argument("--model", help="底层模型 id")
    p.add_argument("--url", dest="url", help="模型服务地址")
    p.add_argument("--description", help="描述")
    p.add_argument("--privacy", choices=["private", "public"], default="private")
    p.set_defaults(func=cmd_ensure_model)

    p = sub.add_parser("revoke", help="撤销该模型已签出的全部 token")
    p.add_argument("--uid", help="模型 uid，缺省用缓存里的")
    p.add_argument("-y", "--yes", action="store_true")
    p.set_defaults(func=cmd_revoke)

    p = sub.add_parser("channels", help="列出当前账号可编辑的 channel")
    p.add_argument("--search", help="按名字过滤")
    p.add_argument("--json", action="store_true", help="输出原始 JSON")
    p.set_defaults(func=cmd_channels)

    p = sub.add_parser("grant", help="为某个 channel 签发 access token 并缓存")
    p.add_argument("channel", nargs="?", help="channel uid / 列表序号 / 名字片段；省略则交互选择")
    p.add_argument("--book", type=int, default=0, help="限定 book，0 表示不限（默认）")
    p.add_argument("--force", action="store_true", help="即使缓存未过期也重新签发")
    p.set_defaults(func=cmd_grant)

    p = sub.add_parser("write", help="写入句子")
    p.add_argument("file", help="句子 JSON 文件，- 表示从 stdin 读")
    p.add_argument("--channel", help="目标 channel（uid / 序号 / 名字片段）")
    p.add_argument("--book", type=int, help="access token 的 book 范围，缺省按句子推断")
    p.add_argument("--batch", type=int, default=DEFAULT_BATCH, help=f"每批条数，默认 {DEFAULT_BATCH}")
    p.add_argument("--content-type", default="markdown")
    p.add_argument("--preview", type=int, default=5, help="确认时预览几条")
    p.add_argument("--dry-run", action="store_true", help="只做校验与回显，不发请求")
    p.add_argument("-y", "--yes", action="store_true", help="跳过交互确认（非交互环境必须显式给）")
    p.set_defaults(func=cmd_write)

    return parser


def main(argv=None):
    args = build_parser().parse_args(argv)
    try:
        return args.func(args)
    except WpError as exc:
        print(f"错误：{exc}", file=sys.stderr)
        return 1
    except KeyboardInterrupt:
        print("\n已中断。", file=sys.stderr)
        return 130


if __name__ == "__main__":
    sys.exit(main())
