import { Button, Layout, Space } from "antd";
import { Navigate, Outlet } from "react-router";
import { useState } from "react";
import { MenuFoldOutlined, MenuUnfoldOutlined } from "@ant-design/icons";
import MainMenu from "../../components/navigation/MainMenu";
import SignInAvatar from "../../components/auth/SignInAvatar";
import HeaderBreadcrumb from "../../components/navigation/HeaderBreadcrumb";
import ThemeSwitch from "../../components/theme/ThemeSwitch";
import { NetworkStatus } from "../../components/general/NetworkStatus";
import { useAuth } from "../../hooks/useAuth";
import UiLangSelect from "../../components/general/UiLangSelect";
import SentCart from "../../components/sentence/SentCart";
import NotificationIcon from "../../components/notification/NotificationIcon";

const { Sider, Content } = Layout;
const Widget = () => {
  const [collapsed, setCollapsed] = useState(false);
  const { isAuthenticated } = useAuth();

  if (!isAuthenticated) {
    return <Navigate to="/anonymous/sign-in" replace />;
  }

  return (
    <Layout style={{ minHeight: "100vh" }}>
      <Sider
        style={{ backgroundColor: "unset" }}
        trigger={null}
        collapsible
        collapsed={collapsed}
      >
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            padding: 4,
          }}
        >
          <SignInAvatar hideName={collapsed} />
          <Button
            type="text"
            icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />}
            onClick={() => setCollapsed(!collapsed)}
          />
        </div>
        <MainMenu />
      </Sider>
      <Layout>
        <div
          style={{
            padding: 16, // 建议保留左右内边距，否则内容会贴边
            display: "flex",
            alignItems: "center", // 垂直居中
            height: 24,
            justifyContent: "space-between", // 如果需要左右分布（如左侧面包屑，右侧头像）可开启
          }}
        >
          <HeaderBreadcrumb />
          <Space size="middle">
            <SentCart />
            <NotificationIcon />
            <NetworkStatus />
            <ThemeSwitch />
            <UiLangSelect />
          </Space>
        </div>

        <Content style={{ padding: 12 }}>
          <Outlet />
        </Content>
      </Layout>
    </Layout>
  );
};

export default Widget;
