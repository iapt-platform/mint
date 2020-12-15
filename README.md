# [PCD-Suite](https://github.com/iapt-platform/PCD-Suite)的 Rust&React 重写版

## 开发

### 工具


推荐使用 **VSCode**, 常用插件列表

- [ESLint](https://github.com/Microsoft/vscode-eslint)
- [Rust(rls)](https://github.com/rust-lang/rls-vscode)
- [Icons](https://github.com/vscode-icons/vscode-icons)
- [OneDark Pro](https://github.com/Binaryify/OneDark-Pro)
- [Better Toml](https://github.com/bungcip/better-toml)
- [Prettier - Code formatter](https://github.com/prettier/prettier-vscode)
- [SQL Formatter](https://github.com/kufii/vscode-sql-formatter)

### Git commit message 规范

- 遵循 git pull request 流程
- 所有代码必须经过格式化
- 禁止提交测试中间数据、key、db 等文件
- Git commit message 格式
  - 每条 message 不超过一行
  - 每个 commit 应该是独立的一个 issue 任务
  - message 格式 `:code: message body`
  - code 定义
    - bug fix: `:bug:`
    - new feature: `:construction:`
    - document: `:pencil:`
    - ops: `:rocket:`
    - config file: `:wrench:`
    - test case: `::white_check_mark:`

### 资源格式

- 文字统一使用**markdown**格式
- 图片
  - 尽量使用**png**格式
  - 如果是 svg 或 graphiz 绘图，**附带源代码**
  - **禁止**使用未经授权的图片

## 部署

```bash
$ ssh-copy-id deploy@xxx.xxx.xxx.xxx
$ RUST_LOG=info axis -i staging -r deploy
```

## 文档

### 后端

- [MinIO is a high performance object storage server compatible with Amazon S3 APIs](https://github.com/minio/minio)
- [Diesel: A safe, extensible ORM and Query Builder for Rust](https://github.com/diesel-rs/diesel)
- [Actix web is a small, pragmatic, and extremely fast rust web framework](https://github.com/actix/actix-web)

### 前端

- [Pluggable enterprise-level react application framework](https://umijs.org/)
- [Ant Desigh Pro](https://pro.ant.design/docs/getting-started)
- [Third-Party Libraries](https://ant.design/docs/react/recommendation)

### Git

- [About git pull request](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/about-pull-requests)
- [how to write a git commit message:](https://chris.beams.io/posts/git-commit/)
- [An emoji guide for your commit messages](https://gitmoji.carloscuesta.me/)
