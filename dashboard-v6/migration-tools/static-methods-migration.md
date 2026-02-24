# 🚨 静态方法迁移专项指南

## ⚠️ 重要警告

你的项目大量使用了以下静态方法：
- `message.success()`, `message.error()`, `message.warning()`, `message.info()`, `message.loading()`
- `notification.success()`, `notification.error()`, `notification.warning()`, `notification.info()`, `notification.open()`
- `Modal.confirm()`, `Modal.info()`, `Modal.success()`, `Modal.error()`, `Modal.warning()`

**这是 v4 → v6 迁移中最重要的变更之一！**

在 v6 中，这些静态方法**必须**在特定的上下文中使用，否则会出现以下问题：
- ❌ 调用后没有任何反应
- ❌ 控制台警告: `instance is null`
- ❌ 主题样式不生效
- ❌ 国际化不生效

---

## 📊 迁移方案对比

### ❌ v4 方式（不推荐）

```typescript
import { message, notification, Modal } from 'antd';

// 直接调用静态方法
const handleClick = () => {
  message.success('操作成功');
  
  notification.info({
    message: '通知标题',
    description: '这是通知内容',
  });
  
  Modal.confirm({
    title: '确认删除？',
    onOk: () => { /* ... */ }
  });
};
```

**问题：** 在 v6 中这些方法需要 Context 才能正常工作！

---

### ✅ v6 方式 - 方案 1: 使用 App.useApp() Hook（推荐）

```typescript
import { App, Button } from 'antd';

const MyComponent = () => {
  const { message, notification, modal } = App.useApp();

  const handleClick = () => {
    message.success('操作成功');
    
    notification.info({
      message: '通知标题',
      description: '这是通知内容',
    });
    
    modal.confirm({
      title: '确认删除？',
      onOk: () => { /* ... */ }
    });
  };

  return <Button onClick={handleClick}>点击</Button>;
};

export default MyComponent;
```

**前提：** 必须在根组件包裹 `<App>` 组件：

```typescript
// src/index.tsx 或 App.tsx
import { App } from 'antd';

const Root = () => {
  return (
    <App>
      <YourApp />
    </App>
  );
};
```

---

### ✅ v6 方式 - 方案 2: 使用独立 Hook（推荐）

对于 message 和 notification，antd 还提供了独立的 hook：

```typescript
import { message, notification } from 'antd';

const MyComponent = () => {
  const [messageApi, messageContextHolder] = message.useMessage();
  const [notificationApi, notificationContextHolder] = notification.useNotification();

  const handleClick = () => {
    messageApi.success('操作成功');
    
    notificationApi.info({
      message: '通知标题',
      description: '这是通知内容',
    });
  };

  return (
    <>
      {messageContextHolder}
      {notificationContextHolder}
      <Button onClick={handleClick}>点击</Button>
    </>
  );
};
```

**缺点：** 每个组件都要声明，比较繁琐。

---

### ✅ v6 方式 - 方案 3: 全局单例（兼容性方案）

如果你的项目中有太多地方使用静态方法，一个一个改太麻烦，可以创建全局单例：

```typescript
// src/utils/antd-global.ts
import { message, notification, Modal } from 'antd';
import type { MessageInstance } from 'antd/es/message/interface';
import type { NotificationInstance } from 'antd/es/notification/interface';
import type { HookAPI } from 'antd/es/modal/useModal';

let messageInstance: MessageInstance;
let notificationInstance: NotificationInstance;
let modalInstance: HookAPI;

// 初始化实例
export const setMessageInstance = (instance: MessageInstance) => {
  messageInstance = instance;
};

export const setNotificationInstance = (instance: NotificationInstance) => {
  notificationInstance = instance;
};

export const setModalInstance = (instance: HookAPI) => {
  modalInstance = instance;
};

// 导出全局 message
export const globalMessage = {
  success: (content: string, duration?: number) => {
    if (!messageInstance) {
      console.error('Message instance not initialized');
      return;
    }
    return messageInstance.success(content, duration);
  },
  error: (content: string, duration?: number) => {
    if (!messageInstance) {
      console.error('Message instance not initialized');
      return;
    }
    return messageInstance.error(content, duration);
  },
  warning: (content: string, duration?: number) => {
    if (!messageInstance) {
      console.error('Message instance not initialized');
      return;
    }
    return messageInstance.warning(content, duration);
  },
  info: (content: string, duration?: number) => {
    if (!messageInstance) {
      console.error('Message instance not initialized');
      return;
    }
    return messageInstance.info(content, duration);
  },
  loading: (content: string, duration?: number) => {
    if (!messageInstance) {
      console.error('Message instance not initialized');
      return;
    }
    return messageInstance.loading(content, duration);
  },
};

// 导出全局 notification
export const globalNotification = {
  success: (config: any) => {
    if (!notificationInstance) {
      console.error('Notification instance not initialized');
      return;
    }
    return notificationInstance.success(config);
  },
  error: (config: any) => {
    if (!notificationInstance) {
      console.error('Notification instance not initialized');
      return;
    }
    return notificationInstance.error(config);
  },
  warning: (config: any) => {
    if (!notificationInstance) {
      console.error('Notification instance not initialized');
      return;
    }
    return notificationInstance.warning(config);
  },
  info: (config: any) => {
    if (!notificationInstance) {
      console.error('Notification instance not initialized');
      return;
    }
    return notificationInstance.info(config);
  },
  open: (config: any) => {
    if (!notificationInstance) {
      console.error('Notification instance not initialized');
      return;
    }
    return notificationInstance.open(config);
  },
};

// 导出全局 modal
export const globalModal = {
  confirm: (config: any) => {
    if (!modalInstance) {
      console.error('Modal instance not initialized');
      return;
    }
    return modalInstance.confirm(config);
  },
  info: (config: any) => {
    if (!modalInstance) {
      console.error('Modal instance not initialized');
      return;
    }
    return modalInstance.info(config);
  },
  success: (config: any) => {
    if (!modalInstance) {
      console.error('Modal instance not initialized');
      return;
    }
    return modalInstance.success(config);
  },
  error: (config: any) => {
    if (!modalInstance) {
      console.error('Modal instance not initialized');
      return;
    }
    return modalInstance.error(config);
  },
  warning: (config: any) => {
    if (!modalInstance) {
      console.error('Modal instance not initialized');
      return;
    }
    return modalInstance.warning(config);
  },
};
```

在 AppProvider 中初始化：

```typescript
// src/AppProvider.tsx
import React from 'react';
import { ConfigProvider, App as AntdApp, theme } from 'antd';
import { 
  setMessageInstance, 
  setNotificationInstance, 
  setModalInstance 
} from './utils/antd-global';

const AppProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const InitInstances = () => {
    const { message, notification, modal } = AntdApp.useApp();
    
    React.useEffect(() => {
      setMessageInstance(message);
      setNotificationInstance(notification);
      setModalInstance(modal);
    }, [message, notification, modal]);
    
    return null;
  };

  return (
    <ConfigProvider>
      <AntdApp>
        <InitInstances />
        {children}
      </AntdApp>
    </ConfigProvider>
  );
};

export default AppProvider;
```

在业务代码中使用：

```typescript
// 任意组件中
import { globalMessage, globalNotification, globalModal } from '@/utils/antd-global';

const handleClick = () => {
  globalMessage.success('操作成功');
  
  globalNotification.info({
    message: '通知标题',
    description: '内容',
  });
  
  globalModal.confirm({
    title: '确认？',
    onOk: () => {},
  });
};
```

**优点：**
- ✅ 最小改动，只需替换 import
- ✅ 适合代码量大、使用广泛的场景
- ✅ 保持原有调用方式

**缺点：**
- ⚠️ 不是官方推荐的最佳实践
- ⚠️ 需要额外维护全局单例代码

---

## 🎯 推荐的迁移策略

根据你的项目规模（581 个文件），我**强烈推荐使用方案 3（全局单例）**，原因：

1. **改动最小** - 只需替换 import 语句
2. **风险最低** - 不需要修改每个组件的逻辑
3. **可逐步优化** - 迁移完成后，可以逐步改为 `App.useApp()`

### 迁移步骤

#### Step 1: 创建全局单例工具

```bash
# 在你的新项目中创建
touch src/utils/antd-global.ts
```

复制上面 **方案 3** 的代码到这个文件。

#### Step 2: 更新 AppProvider

修改你的 `src/theme/AppProvider.tsx`（已经由工具生成），添加实例初始化。

#### Step 3: 批量替换 import 语句

使用我提供的工具批量替换：

```bash
# 预览替换
node migration-tools/replace-static-import.js ./src --dry-run

# 执行替换
node migration-tools/replace-static-import.js ./src --backup
```

替换规则：
```typescript
// 旧的 import
import { message } from 'antd';

// 新的 import
import { globalMessage as message } from '@/utils/antd-global';
```

这样你的业务代码**几乎不需要修改**！

---

## 📝 各方案适用场景

| 方案 | 适用场景 | 改动量 | 推荐度 |
|------|---------|--------|--------|
| 方案 1: App.useApp() | 新项目、小项目 | 大 | ⭐⭐⭐⭐⭐ (官方推荐) |
| 方案 2: 独立 Hook | 少量使用 | 大 | ⭐⭐⭐ |
| 方案 3: 全局单例 | 大项目、使用广泛 | 小 | ⭐⭐⭐⭐ (过渡方案) |

**你的情况：**
- ✅ 项目规模大（581 个文件）
- ✅ 大量使用静态方法
- ✅ 建议使用**方案 3**先快速迁移
- ✅ 后续逐步优化为方案 1

---

## 🔧 迁移检查清单

### Phase 1: 准备工作
- [ ] 创建 `src/utils/antd-global.ts`
- [ ] 更新 `AppProvider.tsx` 初始化实例
- [ ] 确保根组件包裹了 `<App>`

### Phase 2: 批量替换
- [ ] 备份代码（使用 Git）
- [ ] 运行替换脚本（--dry-run）
- [ ] 检查预览结果
- [ ] 执行实际替换（--backup）

### Phase 3: 测试验证
- [ ] 测试 message.success/error/warning/info/loading
- [ ] 测试 notification.success/error/warning/info/open
- [ ] 测试 Modal.confirm/info/success/error/warning
- [ ] 检查主题样式是否正确
- [ ] 检查国际化是否正常

### Phase 4: 特殊情况处理
- [ ] 检查异步场景（setTimeout 等）
- [ ] 检查工具类函数中的调用
- [ ] 检查 Redux action/saga 中的调用
- [ ] 检查 request 拦截器中的调用

---

## ⚠️ 常见问题和解决方案

### 问题 1: 调用静态方法没有反应

**原因：** 实例未初始化或 `<App>` 组件未包裹。

**解决：**
```typescript
// 检查 src/index.tsx
import { App } from 'antd';

ReactDOM.render(
  <App>  {/* 必须包裹 */}
    <YourApp />
  </App>,
  document.getElementById('root')
);
```

### 问题 2: 在工具函数中使用静态方法报错

**原因：** 工具函数不在 React 组件中，无法使用 hook。

**解决：** 使用方案 3 的全局单例：

```typescript
// src/utils/api.ts
import { globalMessage } from './antd-global';

export const request = async (url: string) => {
  try {
    const response = await fetch(url);
    globalMessage.success('请求成功');
    return response;
  } catch (error) {
    globalMessage.error('请求失败');
  }
};
```

### 问题 3: 在 Redux Saga 中使用静态方法

**解决：** 同样使用全局单例：

```typescript
// src/store/sagas/user.saga.ts
import { globalMessage } from '@/utils/antd-global';
import { call, put } from 'redux-saga/effects';

function* loginSaga(action: any) {
  try {
    const result = yield call(loginApi, action.payload);
    globalMessage.success('登录成功');
    yield put({ type: 'LOGIN_SUCCESS', payload: result });
  } catch (error) {
    globalMessage.error('登录失败');
  }
}
```

### 问题 4: 在 setTimeout 中使用静态方法失效

**原因：** 异步回调中 Context 可能失效。

**解决：** 使用全局单例方案不受影响：

```typescript
const handleClick = () => {
  setTimeout(() => {
    globalMessage.success('延迟消息');  // ✅ 正常工作
  }, 1000);
};
```

---

## 🚀 快速迁移脚本

我已经为你准备了一个专门的替换脚本 `replace-static-import.js`，它会自动：

1. 检测所有使用 `message`, `notification`, `Modal` 的文件
2. 替换 import 语句为全局单例
3. 保持业务代码不变

使用方法：

```bash
# 预览
node migration-tools/replace-static-import.js ./src --dry-run

# 执行
node migration-tools/replace-static-import.js ./src --backup
```

---

## 📊 迁移工作量评估

基于你的项目：

| 操作 | 预计耗时 |
|------|---------|
| 创建全局单例工具 | 30 分钟 |
| 更新 AppProvider | 15 分钟 |
| 批量替换 import | 1 小时 |
| 测试验证 | 2-3 小时 |
| 处理特殊情况 | 1-2 小时 |
| **总计** | **5-7 小时** |

如果手动一个一个改为 `App.useApp()`，预计需要 **20-30 小时**！

---

## 💡 最佳实践建议

### 短期（迁移阶段）
1. ✅ 使用全局单例快速迁移
2. ✅ 确保所有功能正常
3. ✅ 重点测试异步场景

### 中期（优化阶段）
1. ✅ 对于新开发的组件，使用 `App.useApp()`
2. ✅ 逐步重构核心组件为标准方式
3. ✅ 保持两种方式共存

### 长期（重构阶段）
1. ✅ 所有组件改用 `App.useApp()`
2. ✅ 移除全局单例代码
3. ✅ 完全符合 v6 最佳实践

---

## 🎯 下一步行动

1. **立即执行：**
   - [ ] 创建 `antd-global.ts` 文件
   - [ ] 运行我提供的替换脚本
   - [ ] 测试核心功能

2. **稍后执行：**
   - [ ] 处理特殊场景（工具函数、Saga 等）
   - [ ] 添加单元测试
   - [ ] 更新团队文档

3. **长期计划：**
   - [ ] 新组件使用 `App.useApp()`
   - [ ] 逐步重构旧组件
   - [ ] 最终移除全局单例

---

**记住：迁移不是一蹴而就的，先让代码跑起来，再逐步优化！** 🚀
