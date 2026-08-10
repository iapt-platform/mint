"""HTTP 客户端：JSON 请求、线上站点之间出声的 fallback、token 显示辅助。"""

import base64
import http.client
import json
import os
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
from datetime import datetime, timezone

from creds import bucket_name_for, get_bucket, load_creds, resolve_api_url, save_creds
from errors import ApiError, WpError
from sites import ONLINE_URLS, SITES, site_label


TOKEN_REFRESH_MARGIN = 3600

DEFAULT_TIMEOUT = 30
WRITE_TIMEOUT = 120
DEFAULT_BATCH = 50


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
    # 路径里可能有巴利词（parivāsa），urllib 只接受 ASCII，必须先百分号编码
    url = api_url + "/" + urllib.parse.quote(path.lstrip("/"), safe="/")
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
                "尚未登录。执行 wikipali-login 即可——没有终端时它会弹出系统密码框，\n"
                "密码由你直接输给操作系统，AI 看不到。\n"
                "（若命令不在 PATH 上，用 ${CLAUDE_PLUGIN_ROOT}/bin/wikipali-login，"
                "或重启会话让 PATH 生效。）"
            )
        return token

    @property
    def model(self):
        model = self.bucket.get("model") or {}
        if not model.get("token"):
            raise WpError("尚未取得模型身份 token。请先跑：wikipali ensure-model --name <模型名>")
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
            except (urllib.error.URLError, TimeoutError, OSError,
                    http.client.HTTPException) as exc:
                # IncompleteRead 属于 HTTPException 而非 OSError——大响应（如两百多万
                # 字符的术语表）传输中断时会走到这里。不捕获的话会抛裸 traceback。
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
