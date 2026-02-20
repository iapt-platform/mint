import React, { useEffect } from "react";
import { ConfigProvider, App as AntdApp, theme } from "antd";
import zhCN from "antd/locale/zh_CN";
import {
  setMessageInstance,
  setNotificationInstance,
  setModalInstance,
} from "../utils/antd-global";

const InitGlobalInstances: React.FC = () => {
  const { message, notification, modal } = AntdApp.useApp();

  useEffect(() => {
    setMessageInstance(message);
    setNotificationInstance(notification);
    setModalInstance(modal);
  }, [message, notification, modal]);

  return null;
};

const AppProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  return (
    <ConfigProvider
      locale={zhCN}
      theme={{
        algorithm: theme.defaultAlgorithm,
        token: {
          colorPrimary: "#1890ff",
          borderRadius: 6,
        },
        components: {
          Button: {
            controlHeight: 32,
          },
        },
      }}
    >
      <AntdApp>
        <InitGlobalInstances />
        {children}
      </AntdApp>
    </ConfigProvider>
  );
};

export default AppProvider;
