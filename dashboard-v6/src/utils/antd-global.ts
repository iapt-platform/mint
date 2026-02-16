import { message, notification, Modal } from 'antd';
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
    console.error(`❌ ${name} instance not initialized. Did you forget to wrap <App> component?`);
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
