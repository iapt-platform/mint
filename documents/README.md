# 关于项目

本项目 wikipāḷi 是巴利圣典教育开放平台（IAPT Platform）的重要构成部分，当前承载着平台所提供的主要能力。

wikipāḷi 的前身是 PCD-Suite，一个使用 PHP + JQuery + SQLite 架构的网页版工具，当前处于试运行环境的服务，就是运行的该版本代码。具体内容参见 [app 目录](../app/
)。

# 当前任务

当前以解决问题为目标，在最小化成本、保证项目运行不中断的前提下，进行重构。

# 后端文档

参见 [documents/api](./api/) 目录，虽然文档尚未完善，但已包含了几乎所有的数据模型和梳理后的 API 接口。

# 前端文档

参见 [documents/dashboard](./dashboard/) 目录，虽然文档尚未完善，但包含了相对完整的网页地图，可供参考。

# 开发规范

> 该规范尚未完善，会在团队协作的过程中，持续迭代优化。

- GitHub 相关
  - Git Commit Message
    使用 [gitmoji](https://gitmoji.dev/) 约定。

    为保证兼容性，请注意使用 `:memo:` 代码模式，而不要直接输入表情符号 `📝`。
  - Pull Request
    为降低 Review 成本，请尽量缩减每个 Pull Request 内包含的 Commits 数量，

    保证每个 Pull Request 仅处理一个问题，或者互相有关联的一些问题。
  - 提交前整理你的 Commits 记录
    不要提交多个相同名称的 Commits，请在 Push 之前对其进行整理。

    方法参考：https://git-scm.com/book/en/v2/Git-Tools-Rewriting-History
  - 不要提交测试数据、测试代码
    请在提交前，删除测试数据或代码。

    如有无用代码，请直接删除，不要仅仅是注释掉（不用担心代码丢失，Git 会记录下一切）。
  - ISSUE
    沟通确定的计划任务，请添加 task 标签，

    并放置于 [Project](https://github.com/orgs/iapt-platform/projects/5) 面板内。
- 编码相关
  - 代码格式化
    请在提交前进行代码格式化。

    这个会在后期通过 [pre-commit](https://pre-commit.com/) 的方式进行统一，当前请与一同协作的开发者联系确定格式化方案。

# 技术选型

以问题出发，进行技术选型，避免长时间大重构，保证小步推进，逐步替换。

以下为对应问题的技术选型变更：

- 为解决数据库效率问题
  - [x] SQLite 替换为 PostgreSQL
  - 参考资料：
    - https://wiki.postgresql.org/wiki/Converting_from_other_Databases_to_PostgreSQL


- 全文检索效率优化
  - [ ] 方案尚未确定（初步考虑基于 PostgreSQL 特性）
  - 参考资料：
    - https://www.postgresql.org/docs/current/datatype-textsearch.html
    - https://www.postgresql.org/docs/current/functions-json.html

- 为解决权限模块安全问题
  - [ ] 方案尚未确定

- 为解决编辑器运行效率问题
  - [ ] 方案尚未确定

- 为解决编辑器可扩展问题
  - [ ] 方案尚未确定

# 团队角色

在当前开发团队人员较少的情况下，一切事务由 [Visuddhinanda](mailto:visuddhinanda@gmail.com "Email") 负责，包括：

- 代码管理
- 网站运维
- 内容审核
- 团队沟通
- 其他事项

# 部署方案

在未完成部署自动化之前，一切部署工作由 [Visuddhinanda](mailto:visuddhinanda@gmail.com "Email") 负责。
