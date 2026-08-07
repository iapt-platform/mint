# wikipali

[WikiPali](https://www.wikipali.org) 巴利三藏平台的 Claude Code 插件。

两个 skill：

- **`research`** —— 检索与阅读语料做研究：词形展开、按词形检索、出处分布（分本文/义注/复注）、按坐标取原文与各家译本。只读，不需要登录。
- **`write`** —— 以 **AI 模型身份**把句子写入句子库。

写入的句子 `editor_uid` 记为 AI 模型的 uid 而不是操作者本人，署名与审计因此是准确的——谁翻的就是谁翻的。

## 安装

```
/plugin marketplace add iapt-platform/wikipali-plugins
/plugin install wikipali@wikipali
```

桌面版在 **Code** 标签页里点 `+` → **Plugins** → **Add plugin** 也可以装。

不走 marketplace 的话，克隆本仓库后跑 `plugins/wikipali/install.sh --user`。

## 装之前请知道它会做什么

插件能在你的机器上执行代码，装之前你应当知道这一个具体会干什么：

- **读写 `~/.wikipali/credentials.json`**（权限 0600），里面存你的 WikiPali 登录 token、AI 模型身份 token 和 channel access token；
- **往 wikipali.org 写数据**。写入是覆盖式的：相同位置（book / paragraph / word_start / word_end / channel）的已有句子会被替换。插件在每次写入前会回显目标并要求确认；
- **只用 Python 标准库**，不装任何依赖，不建虚拟环境。

它**不会**接触你的密码：登录由 `wikipali-login` 完成，密码只读入内存，不落盘、不进日志、不进对话。

- 有终端时用 `getpass` 读；
- **没有终端时（Claude Desktop、IDE、AI 代跑）自动弹出操作系统的密码对话框**——你把密码输给系统，AI 全程看不到；
- 两者都不可用时它会明确报错并给出办法（Claude Desktop 按 <kbd>Ctrl</kbd>+<kbd>`</kbd> 有内置终端，仅本地会话；SSH 场景凭据在远端，要在那台机器上登录）。

登录是一次性的：token 有效期 365 天，同一台机器上所有副本共用 `~/.wikipali/credentials.json`。

## 用法

装好后直接对 Claude 说「把这些译文写进 WikiPali 的某某 channel」即可，它会自己走完流程。手工调用：

```bash
wikipali whoami        # 看当前凭据状态
wikipali-login         # 登录（自己跑）
wikipali ensure-model --name <模型标识>
wikipali channels
wikipali write sents.json --channel <uid> --dry-run
```

句子文件的形状：

```json
{
  "channel_uid": "<channel uid>",
  "sentences": [
    { "book_id": 1, "paragraph": 10, "word_start": 0, "word_end": 12,
      "content": "译文", "content_type": "markdown" }
  ]
}
```

## 站点

线上四个地址（`www` / `next` × `.org` / `.cc`）共享同一个数据库和密钥，凭据通用，可随时切换：

```bash
wikipali endpoint          # 列出并标出当前
wikipali endpoint next     # 改默认
wikipali --api next ...    # 只影响这一次调用
```

`www` 是稳定版、`next` 是最新版**代码**，不是不同的数据环境。较新的端点在稳定版上返回 404，意思是「该站点代码版本还没到」。

## 权限模型

三种 token，职责不混：

| Token | 代表谁 | 有效期 |
|---|---|---|
| 用户 token | 人类操作者 | 365 天 |
| 模型 token | AI 模型身份，写句子时的 `Authorization` | 30 天，可撤销 |
| access token | 被委托的 channel 编辑权，写句子时的 body 字段 | 7 天 |

模型自身不是任何 channel 的 owner，它的全部写权限来自你签发的 access token，且受 book 范围约束——**你没有编辑权的 channel，签发阶段就会失败**。凭据泄漏时用 `wikipali revoke` 作废该模型已签出的全部 token。

## 开发

本插件在 [iapt-platform/mint](https://github.com/iapt-platform/mint) 的 `plugins/wikipali/` 下开发，与被调用的 Laravel API（`api-v13/`）同仓演进——API 契约一改，插件在同一个提交里跟上。设计文档在 `docs/wikipali-write-skill-design.md`。

端点细节见 `references/api-read.md` 与 `references/api-write.md`；跨 skill 的通用约定（坐标、引用格式、文献层次、译文来源判定）见 `references/conventions.md`。

## License

MIT
