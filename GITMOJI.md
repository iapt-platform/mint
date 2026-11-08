# 提交信息约定：gitmoji

mint 仓库（含 api-v13、dashboard-v6、openapi 等所有子目录）的 commit 标题统一用 [gitmoji](https://gitmoji.dev/) 表示提交意图。
2026-11-06 起执行，之前的提交不改写。

表情列表整理自 gitmoji 官方数据 [`gitmojis.json`](https://github.com/carloscuesta/gitmoji/blob/master/packages/gitmojis/src/gitmojis.json)（2026-11-06 抓取，共 75 个），
格式规范整理自 <https://gitmoji.dev/specification>。gitmoji 以后增删表情，以官网为准。

## 格式

官方规范：

```
<intention> [scope?][:?] <message>
```

- **intention**：表达提交意图的 emoji，从下面的列表里选。
- **scope**：可选，说明改动范围。
- **message**：简短说明改了什么。

mint 的写法：

```
✨ (library): 三藏栏目按译本浏览，作品 = book_titles.sn

正文：为什么改、改了哪些行为、有没有要注意的副作用（中文）。

Co-Authored-By: …（由 AI 协作提交时）
```

- **用 Unicode emoji，不用 `:shortcode:`**。终端里的 `git log` 直接能看，标题也更短。
- **emoji 代替 `feat` / `fix` 等类型词**，不要两个都写（不写 `✨ feat(library): …`）。
- **范围放括号里、后跟冒号**，与 `git log` 里已有的范围名保持一致，方便检索：
  `api-v13`、`dashboard-v6`、`openapi`、`api-test`、`dashboard-test`、`plugin`，
  以及 api-v13 内部的模块：`library`、`reader`、`search`、`mq`、`export`、`annotation`、`term`、`progress`、`i18n`。
  跨多个范围就省略范围，不要硬凑。
- **一个 commit 只选一个 emoji**，选最能代表主要意图的那个。
- 标题与正文用中文，标识符、路径、命令保持原文。

## mint 常用对照

从旧的 `type(scope):` 写法换过来：

| 旧写法 | 用 | 说明 |
|---|---|---|
| `feat` | ✨ | 新功能 |
| `fix` | 🐛 | 修 bug |
| `fix`（线上急修，1 小时内必须修好） | 🚑️ | 对应 CLAUDE.md「改 v2 只有两个理由」的第 ① 条 |
| `fix`（小问题、不急） | 🩹 | |
| `docs` | 📝 | |
| `refactor` | ♻️ | 「渐进式还债」拆出来的第二个 commit 用这个 |
| `test` | ✅ | |
| `perf` | ⚡️ | |
| `style`（代码格式，如 pint） | 🎨 | |
| `style`（界面样式） | 💄 | CSS、blade 里的视觉调整 |
| `chore`（配置） | 🔧 | `config/`、`.env.example` 等 |
| `chore`（脚本） | 🔨 | `scripts/`、`tools/` |

mint 里经常遇到、值得记住的几个：

| emoji | 什么时候用 |
|---|---|
| 🗃️ | migration、刷库、改数据（加列、加索引） |
| 🚚 | 改路由 / 改名 / 移动文件，例如 v2 → v3 改名、旧地址 301 |
| 💥 | 破坏性变更：v3 端点改了入参出参、删了字段 |
| 🔥 | 删除代码或文件 |
| ⚰️ | 删死代码（没有调用方的方法、不存在的视图） |
| 🌐 | `resources/lang`、`dashboard-v6/src/locales` 的翻译 |
| 💬 | 改界面文案（不涉及多语言结构） |
| 🏷️ | 重新生成 `schema.d.ts`、改 TypeScript 类型、Resource 的 `@return array{…}` |
| 🦺 | FormRequest 校验规则 |
| 🛂 | 权限判定、`ChecksChannelEditPower`、agent 授权 |
| 🍱 | 图片等静态资源（如 hero 图） |
| 📱 | 移动端 / 窄屏适配 |
| 🚸 | 改进易用性 |
| 🥅 | 捕获错误、404 代替 500 |
| ⬆️ / ➕ / ➖ | 升级 / 新增 / 移除依赖（依赖变更须先经用户同意） |
| 🔊 / 🔇 | 加 / 删日志 |
| 🧑‍💻 | 改进开发体验（skill、本地工具链） |

## 完整列表

| emoji | 短码 | 中文说明 | 官方说明 |
|---|---|---|---|
| 🎨 | `:art:` | 改进代码结构 / 格式 | Improve structure / format of the code. |
| ⚡️ | `:zap:` | 提升性能 | Improve performance. |
| 🔥 | `:fire:` | 删除代码或文件 | Remove code or files. |
| 🐛 | `:bug:` | 修复 bug | Fix a bug. |
| 🚑️ | `:ambulance:` | 紧急热修复 | Critical hotfix. |
| ✨ | `:sparkles:` | 引入新功能 | Introduce new features. |
| 📝 | `:memo:` | 添加或更新文档 | Add or update documentation. |
| 🚀 | `:rocket:` | 部署 | Deploy stuff. |
| 💄 | `:lipstick:` | 添加或更新 UI 与样式文件 | Add or update the UI and style files. |
| 🎉 | `:tada:` | 项目开端 | Begin a project. |
| ✅ | `:white_check_mark:` | 添加、更新测试或让测试通过 | Add, update, or pass tests. |
| 🔒️ | `:lock:` | 修复安全或隐私问题 | Fix security or privacy issues. |
| 🔐 | `:closed_lock_with_key:` | 添加或更新密钥 | Add or update secrets. |
| 🔖 | `:bookmark:` | 发版 / 版本标签 | Release / Version tags. |
| 🚨 | `:rotating_light:` | 修复编译器 / linter 警告 | Fix compiler / linter warnings. |
| 🚧 | `:construction:` | 进行中的工作 | Work in progress. |
| 💚 | `:green_heart:` | 修复 CI 构建 | Fix CI Build. |
| ⬇️ | `:arrow_down:` | 降级依赖 | Downgrade dependencies. |
| ⬆️ | `:arrow_up:` | 升级依赖 | Upgrade dependencies. |
| 📌 | `:pushpin:` | 把依赖锁定到指定版本 | Pin dependencies to specific versions. |
| 👷 | `:construction_worker:` | 添加或更新 CI 构建系统 | Add or update CI build system. |
| 📈 | `:chart_with_upwards_trend:` | 添加或更新统计 / 埋点代码 | Add or update analytics or track code. |
| ♻️ | `:recycle:` | 重构代码 | Refactor code. |
| ➕ | `:heavy_plus_sign:` | 添加依赖 | Add a dependency. |
| ➖ | `:heavy_minus_sign:` | 移除依赖 | Remove a dependency. |
| 🔧 | `:wrench:` | 添加或更新配置文件 | Add or update configuration files. |
| 🔨 | `:hammer:` | 添加或更新开发脚本 | Add or update development scripts. |
| 🌐 | `:globe_with_meridians:` | 国际化与本地化 | Internationalization and localization. |
| ✏️ | `:pencil2:` | 修正错别字 | Fix typos. |
| 💩 | `:poop:` | 写了需要改进的烂代码 | Write bad code that needs to be improved. |
| ⏪️ | `:rewind:` | 回退改动 | Revert changes. |
| 🔀 | `:twisted_rightwards_arrows:` | 合并分支 | Merge branches. |
| 📦️ | `:package:` | 添加或更新编译产物或包 | Add or update compiled files or packages. |
| 👽️ | `:alien:` | 因外部 API 变化而更新代码 | Update code due to external API changes. |
| 🚚 | `:truck:` | 移动或重命名资源（文件、路径、路由等） | Move or rename resources (e.g.: files, paths, routes). |
| 📄 | `:page_facing_up:` | 添加或更新许可证 | Add or update license. |
| 💥 | `:boom:` | 引入破坏性变更 | Introduce breaking changes. |
| 🍱 | `:bento:` | 添加或更新静态资源 | Add or update assets. |
| ♿️ | `:wheelchair:` | 改进无障碍 | Improve accessibility. |
| 💡 | `:bulb:` | 添加或更新源码注释 | Add or update comments in source code. |
| 🍻 | `:beers:` | 醉酒写代码 | Write code drunkenly. |
| 💬 | `:speech_balloon:` | 添加或更新文案与字面量 | Add or update text and literals. |
| 🗃️ | `:card_file_box:` | 数据库相关改动 | Perform database related changes. |
| 🔊 | `:loud_sound:` | 添加或更新日志 | Add or update logs. |
| 🔇 | `:mute:` | 移除日志 | Remove logs. |
| 👥 | `:busts_in_silhouette:` | 添加或更新贡献者 | Add or update contributor(s). |
| 🚸 | `:children_crossing:` | 改进用户体验 / 易用性 | Improve user experience / usability. |
| 🏗️ | `:building_construction:` | 架构调整 | Make architectural changes. |
| 📱 | `:iphone:` | 响应式设计 | Work on responsive design. |
| 🤡 | `:clown_face:` | 模拟（mock） | Mock things. |
| 🥚 | `:egg:` | 添加或更新彩蛋 | Add or update an easter egg. |
| 🙈 | `:see_no_evil:` | 添加或更新 .gitignore | Add or update a .gitignore file. |
| 📸 | `:camera_flash:` | 添加或更新快照 | Add or update snapshots. |
| ⚗️ | `:alembic:` | 实验性改动 | Perform experiments. |
| 🔍️ | `:mag:` | 改进 SEO | Improve SEO. |
| 🏷️ | `:label:` | 添加或更新类型 | Add or update types. |
| 🌱 | `:seedling:` | 添加或更新 seed 文件 | Add or update seed files. |
| 🚩 | `:triangular_flag_on_post:` | 添加、更新或移除功能开关 | Add, update, or remove feature flags. |
| 🥅 | `:goal_net:` | 捕获错误 | Catch errors. |
| 💫 | `:dizzy:` | 添加或更新动画与过渡 | Add or update animations and transitions. |
| 🗑️ | `:wastebasket:` | 标记待清理的废弃代码 | Deprecate code that needs to be cleaned up. |
| 🛂 | `:passport_control:` | 授权、角色与权限相关代码 | Work on code related to authorization, roles and permissions. |
| 🩹 | `:adhesive_bandage:` | 非紧急问题的简单修复 | Simple fix for a non-critical issue. |
| 🧐 | `:monocle_face:` | 数据探查 / 检查 | Data exploration/inspection. |
| ⚰️ | `:coffin:` | 删除死代码 | Remove dead code. |
| 🧪 | `:test_tube:` | 添加一个（预期）失败的测试 | Add a failing test. |
| 👔 | `:necktie:` | 添加或更新业务逻辑 | Add or update business logic. |
| 🩺 | `:stethoscope:` | 添加或更新健康检查 | Add or update healthcheck. |
| 🧱 | `:bricks:` | 基础设施相关改动 | Infrastructure related changes. |
| 🧑‍💻 | `:technologist:` | 改进开发者体验 | Improve developer experience. |
| 💸 | `:money_with_wings:` | 赞助或资金相关设施 | Add sponsorships or money related infrastructure. |
| 🧵 | `:thread:` | 多线程或并发相关代码 | Add or update code related to multithreading or concurrency. |
| 🦺 | `:safety_vest:` | 校验相关代码 | Add or update code related to validation. |
| ✈️ | `:airplane:` | 改进离线支持 | Improve offline support. |
| 🦖 | `:t-rex:` | 添加向后兼容的代码 | Code that adds backwards compatibility. |
