# Usage

```bash
cd dashboard
# install dependencies
yarn install
# start the dev server: http://localhost:8000/my/
yarn start
```

## Getting Started in **7** days

### Day 1: Prepare

- [TypeScript for JavaScript Programmers](https://www.typescriptlang.org/docs/handbook/typescript-in-5-minutes.html)
- [Install ubuntu@wls(ONLY For Windows User)](https://ubuntu.com/wsl)
- [Start continer](../docker)
- How to use tmux

  ```text
  tmux new -s <session-name> 新建会话
  $ tmux detach 分离会话
  $ tmux ls 查看当前所有的 Tmux 会话
  $ tmux attach -t 0  接入会话
  $ tmux kill-session -t 0 命令用于杀死某个会话。
  $ tmux switch -t 0 切换会话
  ctrl+b [ 滚屏
  Ctrl+b d 分离会话
  Ctrl+b ? 帮助
  Ctrl+b %
  Ctrl+b "
  Ctrl+b up
  Ctrl+b down
  Ctrl+b left
  Ctrl+b right
  Ctrl+b d：分离当前会话。
  Ctrl+b s：列出所有会话。
  Ctrl+b $：重命名当前会话。
  ```

- Start backend & frontend server

### Day 2: Router & Component

- 浏览器打开： `http://localhost:8000/my/demo`
- 路由跳转（history vs Link） **pages/demo/index.tsx**
- 组件复用: **components/demo.ts**
- 路由参数： **pages/demo/[id]/show.tsx**

### Day 3: State & Fetch

### Day 4: Form & Table

### Day 5: Work with http rest api

### Day 6

### Day 7
