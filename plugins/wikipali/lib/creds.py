"""凭据文件：~/.wikipali/credentials.json（0600）。

只存 token，不存密码。线上四地址共用 online 桶，开发机 local，其余自成一桶。
"""

import json
import os
import stat

from errors import WpError
from sites import BUCKET_BY_URL, DEFAULT_API_URL, LOCAL_URL, expand_site_alias, normalize_api_url


CREDS_DIR = os.path.join(os.path.expanduser("~"), ".wikipali")
CREDS_PATH = os.path.join(CREDS_DIR, "credentials.json")


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
    """凭据（与本地缓存）的桶名。

    只有线上四个地址共享同一个库与密钥，共用 online 桶；staging、开发机、以及任何
    自定义地址都是**另一个库**，各自一桶——混用会让凭据失效，更糟的是让查询静默
    落到错误的数据上。
    """
    known = BUCKET_BY_URL.get(api_url)
    if known:
        return known
    return "site:" + api_url


def get_bucket(creds, name, api_url=None):
    bucket = creds.setdefault(name, {})
    bucket.setdefault("api_url", api_url or (DEFAULT_API_URL if name == "online" else LOCAL_URL))
    bucket.setdefault("user", {})
    bucket.setdefault("model", {})
    bucket.setdefault("access_tokens", {})
    return bucket


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
