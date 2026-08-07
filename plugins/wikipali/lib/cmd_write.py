"""写入路径的子命令：ensure-model / revoke / channels / grant / write。"""

import json
import sys
import time
from datetime import datetime, timezone

from client import (WRITE_TIMEOUT, TOKEN_REFRESH_MARGIN, DEFAULT_BATCH,
                    fmt_ts, make_client, mask, note, token_expiry)
from errors import ApiError, WpError, explain_api_error


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
        raise WpError("未指定 channel，且当前不是交互式终端。请先跑 `wikipali channels` 再用 --channel 指定。")

    print("可编辑 channel：")
    for idx, ch in enumerate(rows, 1):
        print(f"  {idx:>2}) {ch.get('name', '')[:32]:<34} {str(ch.get('lang', '')):<6} {ch.get('uid', '')[:8]}…")
    raw = input("选择序号：").strip()
    if not raw.isdigit() or not (1 <= int(raw) <= len(rows)):
        raise WpError("输入无效。")
    ch = rows[int(raw) - 1]
    return ch["uid"], ch.get("name")


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
        raise WpError("缓存里没有模型 uid，无法重签。请跑：wikipali ensure-model --name <模型名>")
    try:
        issued = client.call("GET", f"v2/ai-model-token/{model['uid']}", token=client.user_token)
    except ApiError as exc:
        raise explain_api_error(exc, "重新签发模型 token")
    model.update({"uid": issued["uid"], "name": issued["name"], "token": issued["token"], "issued_at": iso_now()})
    client.bucket["model"] = model
    client.save()
    return issued["token"]
