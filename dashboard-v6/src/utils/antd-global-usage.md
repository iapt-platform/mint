# 全局单例使用示例

## 📁 文件结构

```
src/
├── utils/
│   └── antd-global.ts        ← 全局单例工具
├── theme/
│   └── AppProvider.tsx        ← 更新后的 AppProvider
└── main.tsx                   ← 根组件
```

## 🚀 快速开始

### 1. 在根组件中使用 AppProvider

```typescript
// src/main.tsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { Provider } from 'react-redux';
import store from './store';
import AppProvider from './theme/AppProvider';
import Router from './Router';

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <Provider store={store}>
      <AppProvider>
        <Router />
      </AppProvider>
    </Provider>
  </React.StrictMode>
);
```

### 2. 在业务代码中使用

```typescript
// 任意文件中
import { globalMessage, globalNotification, globalModal } from '@/utils/antd-global';

// 使用
globalMessage.success('操作成功');
globalNotification.info({ message: '通知', description: '内容' });
globalModal.confirm({ title: '确认？', onOk: () => {} });
```

## 📝 API 参考

### globalMessage
- globalMessage.success(content, duration?, onClose?)
- globalMessage.error(content, duration?, onClose?)
- globalMessage.warning(content, duration?, onClose?)
- globalMessage.info(content, duration?, onClose?)
- globalMessage.loading(content, duration?, onClose?)

### globalNotification
- globalNotification.success(config)
- globalNotification.error(config)
- globalNotification.warning(config)
- globalNotification.info(config)
- globalNotification.open(config)

### globalModal
- globalModal.confirm(config)
- globalModal.info(config)
- globalModal.success(config)
- globalModal.error(config)
- globalModal.warning(config)
