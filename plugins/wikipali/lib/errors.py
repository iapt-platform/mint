"""面向用户的错误类型，以及把 HTTP 状态翻译成人话。"""


class WpError(Exception):
    """面向用户的错误：main() 捕获后只打印 message，不打印堆栈。"""


class ApiError(WpError):
    def __init__(self, status, message, url=None, body=None):
        self.status = status
        self.url = url
        self.body = body
        super().__init__(message)


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
