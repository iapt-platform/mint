import { Button, Layout, Space, theme } from "antd";
import { Navigate, Outlet } from "react-router";
import { useCallback, useState } from "react";
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
import TeamspaceBadge from "../../features/teamspace/TeamspaceBadge";
import TeamspaceGuard from "../../features/teamspace/TeamspaceGuard";
import {
  WORKSPACE_CONTENT_PADDING,
  WORKSPACE_HEADER_HEIGHT,
} from "./constants";

const { Sider, Content } = Layout;

/** 主导航折叠状态的 localStorage 键 */
const COLLAPSED_KEY = "workspace:main-menu-collapsed";

const Widget = () => {
  const [collapsed, setCollapsed] = useState<boolean>(() => {
    try {
      return localStorage.getItem(COLLAPSED_KEY) === "true";
    } catch {
      return false;
    }
  });
  const toggleCollapsed = useCallback(() => {
    setCollapsed((v) => {
      const next = !v;
      try {
        localStorage.setItem(COLLAPSED_KEY, String(next));
      } catch {
        /* 静默降级 */
      }
      return next;
    });
  }, []);
  const { token } = theme.useToken();
  const { isAuthenticated } = useAuth();

  if (!isAuthenticated) {
    return <Navigate to="/anonymous/sign-in" replace />;
  }

  return (
    <Layout style={{ minHeight: "100vh" }}>
      <Sider
        style={{
          backgroundColor: "unset",
          position: "sticky",
          top: 0,
          height: "100vh",
          overflowY: "auto",
        }}
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
            onClick={toggleCollapsed}
          />
        </div>
        <MainMenu />
      </Sider>
      <Layout>
        <div
          style={{
            position: "sticky",
            top: 0,
            zIndex: 10,
            background: token.colorBgLayout,
            padding: "0 16px", // 保留左右内边距，否则内容会贴边
            display: "flex",
            alignItems: "center", // 垂直居中
            boxSizing: "border-box",
            height: WORKSPACE_HEADER_HEIGHT,
            justifyContent: "space-between", // 如果需要左右分布（如左侧面包屑，右侧头像）可开启
          }}
        >
          <HeaderBreadcrumb />
          <Space size="middle">
            <TeamspaceBadge />
            <SentCart />
            <NotificationIcon />
            <NetworkStatus />
            <ThemeSwitch />
            <UiLangSelect />
          </Space>
        </div>

        <Content style={{ padding: WORKSPACE_CONTENT_PADDING }}>
          <TeamspaceGuard>
            <Outlet />
          </TeamspaceGuard>
        </Content>
      </Layout>
    </Layout>
  );
};

export default Widget;
