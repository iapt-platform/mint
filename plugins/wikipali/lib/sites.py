"""站点清单与地址解析。

四个线上地址共享同一个数据库和同一把 jwt 密钥，凭据完全通用；
.org / .cc 是地区可达性，www / next 是代码版本（不是数据环境）。
开发机是另一个库、另一把密钥，故单独一桶，且永不作为自动 fallback 目标。
"""

import urllib.parse

from errors import WpError


# bucket 决定两件事：凭据/缓存存哪一桶，以及能不能作为自动 fallback 的目标。
# 只有 bucket == "online" 的四个地址共享同一个数据库与 jwt 密钥，彼此可以互替。
# staging 与开发机各是**另一个库**——实测 staging 的公开 channel 数与线上不同，
# 同名 channel 的 uid 也不同——所以自成一桶，且绝不参与 fallback。
SITES = [
    {"key": "www", "url": "https://www.wikipali.org/api",
     "version": "稳定版", "domain": ".org", "bucket": "online"},
    {"key": "www.cc", "url": "https://www.wikipali.cc/api",
     "version": "稳定版", "domain": ".cc", "bucket": "online"},
    {"key": "next", "url": "https://next.wikipali.org/api",
     "version": "最新版", "domain": ".org", "bucket": "online"},
    {"key": "next.cc", "url": "https://next.wikipali.cc/api",
     "version": "最新版", "domain": ".cc", "bucket": "online"},
    {"key": "staging", "url": "https://staging.wikipali.org/api",
     "version": "预发布", "domain": "独立库", "bucket": "staging"},
    {"key": "local", "url": "http://127.0.0.1:8000/api",
     "version": "开发机", "domain": "独立库", "bucket": "local"},
]

ONLINE_URLS = [s["url"] for s in SITES if s["bucket"] == "online"]
LOCAL_URL = next(s["url"] for s in SITES if s["key"] == "local")
BUCKET_BY_URL = {s["url"]: s["bucket"] for s in SITES}
DEFAULT_API_URL = SITES[0]["url"]


def normalize_api_url(url):
    url = url.rstrip("/")
    parsed = urllib.parse.urlparse(url)
    if parsed.scheme not in ("http", "https"):
        raise WpError(f"API 地址必须以 http:// 或 https:// 开头：{url}")
    host = (parsed.hostname or "").lower()
    if parsed.scheme == "http" and host not in ("127.0.0.1", "localhost", "::1"):
        raise WpError(f"只有 127.0.0.1 / localhost 允许用 http://，其余必须 https://：{url}")
    return url


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
