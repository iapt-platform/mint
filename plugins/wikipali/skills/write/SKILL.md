---
name: write
description: "Use this skill to write sentences (translations, commentary) into the WikiPali sentence database over its HTTP API, from any project. Trigger whenever the user asks to upload, push, publish, sync, or save translated Pali sentences to WikiPali / 巴利文 / wikipali.org, or mentions writing to a WikiPali channel, or asks about wp.py / wp_login.py / ~/.wikipali/credentials.json. Handles login, AI-model identity tokens, channel selection, access tokens, and batched writes with attribution as the AI model rather than the human operator. Do not use for reading WikiPali data or for unrelated Laravel/API work."
metadata:
  author: mint
---

# WikiPali 写入

把句子写进 WikiPali 句子库，**署名为 AI 模型身份**（`editor_uid` = 模型 uid），而不是操作者本人。

两个脚本，只依赖 Python 标准库，直接跑，不要建虚拟环境：

- `scripts/wp_login.py` —— 唯一接触密码的脚本，**必须由用户本人执行**
- `scripts/wp.py` —— 其余全部操作

下面的命令都写成 `python3 scripts/wp.py …`，其中 `scripts/` 是**本 SKILL.md 同目录下的** `scripts/`。执行前先把它换成实际路径：

- 作为插件安装时：`${CLAUDE_PLUGIN_ROOT}/skills/write/scripts/wp.py`
- 直接放在项目里时：`<skill 目录>/scripts/wp.py`

拿不准就先 `ls` 确认，不要凭猜测拼路径。

## 铁律

1. **永远不要向用户索要密码，也不要代跑 `wp_login.py`。** 需要登录时，请用户**另开一个真正的终端**执行
   `python3 <上面那个 scripts 路径>/wp_login.py`（把完整路径写给他们，别让他们自己找）。
   不要让他们用 Claude Code 的 `!` 前缀——那里没有交互式终端，密码提示无处输入；也不要建议把密码放进命令行参数或直接打在对话里。
2. **写入前必须让用户确认。** `wp.py write` 默认会回显目标并等确认；只有用户已经明确同意本次写入时，才可以加 `-y`。
3. **绝不打印 token 全文**（`~/.wikipali/credentials.json` 里的任何值）。脚本自己会打码，不要 `cat` 那个文件。
4. **`count` 不等于提交条数就是有句子没写进去**，必须如实报告给用户，不要说「已全部写入」。
5. **收到 401 不要自动重试**，按脚本的提示走。

## 首次准备

```bash
python3 scripts/wp.py whoami          # 先看缺什么
```

按缺什么补什么：

```bash
# 1) 登录（用户自己在另一个终端里跑，不要用 ! 前缀，也不要代跑）
python3 scripts/wp_login.py

# 2) 建立模型身份并取 token；--name 必须是你自己的模型标识
python3 scripts/wp.py ensure-model --name claude-opus-5

# 3) 看有哪些可写的 channel
python3 scripts/wp.py channels
```

`--name` 决定句子的作者署名，**不要冒用别的模型的名字**。同名记录已存在时会直接复用（幂等）。

## 写入

输入是一个 JSON 文件，两种形状都接受：

```json
{
  "channel_uid": "可选，整批共用的 channel",
  "sentences": [
    { "book_id": 1, "paragraph": 10, "word_start": 0, "word_end": 12,
      "content": "译文", "content_type": "markdown" }
  ]
}
```

或直接是句子数组（此时用 `--channel` 指定目标）。`content_type` 可省略，默认 `markdown`；`channel_uid` 可以逐句给，用于跨 channel 批量写。

```bash
python3 scripts/wp.py write sentences.json --channel <uid或名字片段> --dry-run   # 先看回显
python3 scripts/wp.py write sentences.json --channel <uid或名字片段>            # 再真写
```

`write` 会自动完成：解析校验 → 确定 channel → 回显确认 → 按需签发/复用 access token → 每 50 条一批提交 → 核对 `count` 并报告漏写的句子。

**写入是覆盖式的**：相同 `(book_id, paragraph, word_start, word_end, channel_uid)` 的已有句子会被替换。回显里那行警告要转达给用户。

## 站点

四个线上地址共享同一个数据库和密钥，凭据通用；`www` 是稳定版、`next` 是最新版代码，**不是**不同的数据环境。

```bash
python3 scripts/wp.py endpoint            # 列出并标出当前
python3 scripts/wp.py endpoint next       # 改默认（唯一会写回凭据的方式）
python3 scripts/wp.py --api next write …  # 只影响这一次调用
```

新端点在稳定版上返回 404 是「代码版本还没到」，不是「资源不存在」。

## 出问题时

| 现象 | 处置 |
|---|---|
| 401 | 用户 token 失效 → 请用户重跑 `wp_login.py`；模型 token 失效或被撤销 → 重跑 `ensure-model` |
| 403 | 不是 channel 的 owner/协作者，或不是模型 owner。指出缺哪项权限，别换个姿势重试 |
| `count: 0`（签 access token） | 对该 channel 无编辑权。**中止写入**，不要继续 |
| `count` 小于提交条数 | 逐条差集已由脚本列出，如实转达 |
| 404（`ai-model-token` 等新端点） | 提示切到 `next` 或稍后再试 |

凭据泄漏时撤销模型的全部 token：

```bash
python3 scripts/wp.py revoke
```

## 更多

端点字段、返回形状与各处陷阱见 `references/api.md`。若脚本行为与该文件对不上，多半是这份副本过期了——插件用户跑 `/plugin update`，手工安装的用户重新装一遍。
