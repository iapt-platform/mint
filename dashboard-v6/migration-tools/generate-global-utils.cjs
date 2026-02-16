#!/usr/bin/env node

/**
 * 全局单例工具生成器
 *
 * 功能：生成 antd-global.ts 文件和更新后的 AppProvider
 * 使用方法：node generate-global-utils.js [输出目录]
 */

const fs = require("fs");
const path = require("path");

// antd-global.ts 模板
const antdGlobalTemplate = `import { message, notification, Modal } from 'antd';
import type { MessageInstance } from 'antd/es/message/interface';
import type { NotificationInstance } from 'antd/es/notification/interface';
import type { HookAPI } from 'antd/es/modal/useModal';

/**
 * 全局 antd 实例管理
 * 
 * 用途：为无法使用 hooks 的场景（工具函数、Redux Saga 等）提供全局访问
 * 
 * ⚠️ 注意：这是过渡方案！
 * 推荐在 React 组件中使用 App.useApp() 代替
 * 
 * 使用方法：
 * 1. 在 AppProvider 中初始化实例（见下方示例）
 * 2. 在业务代码中导入使用：
 *    import { globalMessage, globalNotification, globalModal } from '@/utils/antd-global';
 */

let messageInstance: MessageInstance | null = null;
let notificationInstance: NotificationInstance | null = null;
let modalInstance: HookAPI | null = null;

/**
 * 设置 message 实例
 * 在 AppProvider 中调用
 */
export const setMessageInstance = (instance: MessageInstance) => {
  messageInstance = instance;
  console.log('✅ Global message instance initialized');
};

/**
 * 设置 notification 实例
 * 在 AppProvider 中调用
 */
export const setNotificationInstance = (instance: NotificationInstance) => {
  notificationInstance = instance;
  console.log('✅ Global notification instance initialized');
};

/**
 * 设置 modal 实例
 * 在 AppProvider 中调用
 */
export const setModalInstance = (instance: HookAPI) => {
  modalInstance = instance;
  console.log('✅ Global modal instance initialized');
};

/**
 * 检查实例是否已初始化
 */
const checkInstance = (name: string, instance: any) => {
  if (!instance) {
    console.error(\`❌ \${name} instance not initialized. Did you forget to wrap <App> component?\`);
    return false;
  }
  return true;
};

/**
 * 全局 message API
 * 
 * 使用示例：
 * import { globalMessage } from '@/utils/antd-global';
 * globalMessage.success('操作成功');
 */
export const globalMessage = {
  success: (content: string, duration?: number, onClose?: () => void) => {
    if (!checkInstance('Message', messageInstance)) return;
    return messageInstance!.success(content, duration, onClose);
  },
  
  error: (content: string, duration?: number, onClose?: () => void) => {
    if (!checkInstance('Message', messageInstance)) return;
    return messageInstance!.error(content, duration, onClose);
  },
  
  warning: (content: string, duration?: number, onClose?: () => void) => {
    if (!checkInstance('Message', messageInstance)) return;
    return messageInstance!.warning(content, duration, onClose);
  },
  
  info: (content: string, duration?: number, onClose?: () => void) => {
    if (!checkInstance('Message', messageInstance)) return;
    return messageInstance!.info(content, duration, onClose);
  },
  
  loading: (content: string, duration?: number, onClose?: () => void) => {
    if (!checkInstance('Message', messageInstance)) return;
    return messageInstance!.loading(content, duration, onClose);
  },
  
  destroy: () => {
    if (!checkInstance('Message', messageInstance)) return;
    return messageInstance!.destroy();
  },
};

/**
 * 全局 notification API
 * 
 * 使用示例：
 * import { globalNotification } from '@/utils/antd-global';
 * globalNotification.info({
 *   message: '通知标题',
 *   description: '通知内容',
 * });
 */
export const globalNotification = {
  success: (config: any) => {
    if (!checkInstance('Notification', notificationInstance)) return;
    return notificationInstance!.success(config);
  },
  
  error: (config: any) => {
    if (!checkInstance('Notification', notificationInstance)) return;
    return notificationInstance!.error(config);
  },
  
  warning: (config: any) => {
    if (!checkInstance('Notification', notificationInstance)) return;
    return notificationInstance!.warning(config);
  },
  
  info: (config: any) => {
    if (!checkInstance('Notification', notificationInstance)) return;
    return notificationInstance!.info(config);
  },
  
  open: (config: any) => {
    if (!checkInstance('Notification', notificationInstance)) return;
    return notificationInstance!.open(config);
  },
  
  destroy: (key?: string) => {
    if (!checkInstance('Notification', notificationInstance)) return;
    return notificationInstance!.destroy(key);
  },
};

/**
 * 全局 modal API
 * 
 * 使用示例：
 * import { globalModal } from '@/utils/antd-global';
 * globalModal.confirm({
 *   title: '确认删除？',
 *   content: '删除后无法恢复',
 *   onOk: () => { ... },
 * });
 */
export const globalModal = {
  confirm: (config: any) => {
    if (!checkInstance('Modal', modalInstance)) return;
    return modalInstance!.confirm(config);
  },
  
  info: (config: any) => {
    if (!checkInstance('Modal', modalInstance)) return;
    return modalInstance!.info(config);
  },
  
  success: (config: any) => {
    if (!checkInstance('Modal', modalInstance)) return;
    return modalInstance!.success(config);
  },
  
  error: (config: any) => {
    if (!checkInstance('Modal', modalInstance)) return;
    return modalInstance!.error(config);
  },
  
  warning: (config: any) => {
    if (!checkInstance('Modal', modalInstance)) return;
    return modalInstance!.warning(config);
  },
};
`;

// 更新后的 AppProvider 模板（带实例初始化）
const appProviderWithGlobalTemplate = `import React, { useState, useEffect } from 'react';
import { ConfigProvider, App as AntdApp, theme } from 'antd';
import zhCN from 'antd/locale/zh_CN';
import { 
  setMessageInstance, 
  setNotificationInstance, 
  setModalInstance 
} from '../utils/antd-global';

type ThemeMode = 'light' | 'dark';

/**
 * 初始化全局 antd 实例
 * 为工具函数、Redux Saga 等无法使用 hooks 的场景提供支持
 */
const InitGlobalInstances: React.FC = () => {
  const { message, notification, modal } = AntdApp.useApp();
  
  useEffect(() => {
    setMessageInstance(message);
    setNotificationInstance(notification);
    setModalInstance(modal);
  }, [message, notification, modal]);
  
  return null;
};

/**
 * App Provider 组件
 * 
 * v4 → v6 主题系统迁移：
 * - ✅ 使用 ConfigProvider 管理全局配置
 * - ✅ 使用 theme.algorithm 切换主题
 * - ✅ 使用 Design Token 自定义主题
 * - ✅ 使用 App 组件提供全局 message/notification/modal
 * - ✅ 初始化全局实例，支持工具函数调用
 */
const AppProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [themeMode, setThemeMode] = useState<ThemeMode>('light');

  // 从 localStorage 读取保存的主题
  useEffect(() => {
    const savedTheme = localStorage.getItem('theme') as ThemeMode;
    if (savedTheme) {
      setThemeMode(savedTheme);
    }
  }, []);

  // 保存主题到 localStorage
  const handleThemeChange = (mode: ThemeMode) => {
    setThemeMode(mode);
    localStorage.setItem('theme', mode);
  };

  return (
    <ConfigProvider
      locale={zhCN}
      theme={{
        // 主题算法：暗黑或明亮
        algorithm: themeMode === 'dark' ? theme.darkAlgorithm : theme.defaultAlgorithm,
        
        // 全局 Design Token
        token: {
          colorPrimary: '#1890ff',
          borderRadius: 6,
          // TODO: 添加更多自定义 token
        },
        
        // 组件级别的主题定制
        components: {
          Button: {
            controlHeight: 32,
          },
          Card: {
            borderRadiusLG: 8,
          },
          // TODO: 添加更多组件定制
        },
      }}
    >
      <AntdApp>
        {/* 初始化全局实例 - 重要！ */}
        <InitGlobalInstances />
        
        {/* 你可以在这里添加 ThemeSwitch 组件 */}
        {/* <ThemeSwitch onChange={handleThemeChange} /> */}
        
        {children}
      </AntdApp>
    </ConfigProvider>
  );
};

export default AppProvider;
`;

// 使用示例文档
const usageExample = `# 全局单例使用示例

## 📁 文件结构

\`\`\`
src/
├── utils/
│   └── antd-global.ts        ← 全局单例工具
├── theme/
│   └── AppProvider.tsx        ← 更新后的 AppProvider
└── main.tsx                   ← 根组件
\`\`\`

## 🚀 快速开始

### 1. 在根组件中使用 AppProvider

\`\`\`typescript
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
\`\`\`

### 2. 在业务代码中使用

\`\`\`typescript
// 任意文件中
import { globalMessage, globalNotification, globalModal } from '@/utils/antd-global';

// 使用
globalMessage.success('操作成功');
globalNotification.info({ message: '通知', description: '内容' });
globalModal.confirm({ title: '确认？', onOk: () => {} });
\`\`\`

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
`;

// 主函数
function main() {
  const args = process.argv.slice(2);
  const outputDir = args[0] || "./src/utils";

  console.log("\n🔧 生成全局单例工具文件...\n");

  // 创建输出目录
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
    console.log(`✅ 创建目录: ${outputDir}`);
  }

  // 生成 antd-global.ts
  const antdGlobalPath = path.join(outputDir, "antd-global.ts");
  if (fs.existsSync(antdGlobalPath)) {
    console.log(`⚠️  文件已存在: ${antdGlobalPath} (跳过)`);
  } else {
    fs.writeFileSync(antdGlobalPath, antdGlobalTemplate);
    console.log(`✅ 已生成: ${antdGlobalPath}`);
  }

  // 生成更新后的 AppProvider
  const appProviderDir = path.join(outputDir, "../theme");
  if (!fs.existsSync(appProviderDir)) {
    fs.mkdirSync(appProviderDir, { recursive: true });
  }

  const appProviderPath = path.join(appProviderDir, "AppProvider.tsx");
  if (fs.existsSync(appProviderPath)) {
    console.log(`⚠️  文件已存在: ${appProviderPath} (跳过)`);
    console.log("   如需更新，请手动添加 InitGlobalInstances 组件");
  } else {
    fs.writeFileSync(appProviderPath, appProviderWithGlobalTemplate);
    console.log(`✅ 已生成: ${appProviderPath}`);
  }

  // 生成使用示例
  const examplePath = path.join(outputDir, "antd-global-usage.md");
  fs.writeFileSync(examplePath, usageExample);
  console.log(`✅ 已生成: ${examplePath}`);

  console.log("\n" + "=".repeat(80));
  console.log("✅ 全局单例工具生成完成！");
  console.log("=".repeat(80) + "\n");

  console.log("📝 下一步操作:\n");
  console.log("1. 在根组件中使用 AppProvider:");
  console.log(`   import AppProvider from './theme/AppProvider';`);
  console.log("   <AppProvider><YourApp /></AppProvider>\n");

  console.log("2. 运行替换脚本，批量替换 import:");
  console.log("   node replace-static-import.js ./src --dry-run\n");

  console.log("3. 确认无误后执行替换:");
  console.log("   node replace-static-import.js ./src --backup\n");

  console.log("4. 测试所有静态方法是否正常工作\n");

  console.log("📖 详细说明请查看:");
  console.log(`   - ${examplePath}`);
  console.log("   - static-methods-migration.md\n");
}

main();
