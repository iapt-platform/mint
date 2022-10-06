# 前端培训文档

## 安装依赖包

### offline

```bash
# offline
tar xf node_modules.tar.xz
```

### online

```bash
cd dashboard
yarn install
```

- for windows user, download [Node.js](https://nodejs.org/en/download/current/) and then,

  ```bash
  npm config set registry 'https://registry.npmmirror.com/' --global # ONLY for China user
  npm install -g yarn
  yarn config set registry 'https://registry.npmmirror.com/' --global # ONLY for China users
  ```

![dashboard.png](dashboard.png)

## 开发模式启动 [dashboard](http://localhost:3000)

```bash
cd dashboard
yarn start
```

## Typescript

- [Typescript 第一天](ts-day-1/)

## React

- [React 第一天： router & layout](react-day-1/)
- [React 第二天： i18n & form](react-day-2/)
- [React 第三天： table](react-day-3/)
- [React 第四天： http request](react-day-4/)
- [React 第五天： page & component & redux](react-day-5/)
