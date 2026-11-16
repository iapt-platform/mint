import { useState } from "react";
import { Button, Layout, Menu, Result, Space, Spin, type MenuProps } from "antd";
import { Link, Navigate, Outlet, useLocation } from "react-router";
import { useIntl } from "react-intl";
import {
  ApartmentOutlined,
  ApiOutlined,
  BarChartOutlined,
  BookOutlined,
  DashboardOutlined,
  FontSizeOutlined,
  MailOutlined,
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  ReadOutlined,
  RobotOutlined,
  SettingOutlined,
  TeamOutlined,
} from "@ant-design/icons";
import { useAuth } from "../../hooks/useAuth";
import { useAppSelector } from "../../hooks";
import { currentUser, isAdministrator, isGuest, isRoot, TO_SIGN_IN } from "../../reducers/current-user";
import SignInAvatar from "../../components/auth/SignInAvatar";
import HeaderBreadcrumb from "../../components/navigation/HeaderBreadcrumb";
import ThemeSwitch from "../../components/theme/ThemeSwitch";
import UiLangSelect from "../../components/general/UiLangSelect";

const { Sider, Content } = Layout;

/** 菜单项的 key 就是路由；选中项按当前路径的最长前缀匹配。 */
const MENU_PATHS = [
  "/admin/stats/reading",
  "/admin/stats/ai",
  "/admin/api",
  "/admin/relation",
  "/admin/nissaya-ending",
  "/admin/dictionary/preference",
  "/admin/dictionary",
  "/admin/users",
  "/admin/invite",
  "/admin/ai-models",
];

/**
 * 管理后台外壳：左侧菜单 + 顶部面包屑。只有 root / administrator 能进，
 * 其他已登录用户看到 403；没登录跳登录页。
 */
const AdminLayout = () => {
  const intl = useIntl();
  const { pathname } = useLocation();
  const { isAuthenticated } = useAuth();
  const user = useAppSelector(currentUser);
  const guest = useAppSelector(isGuest);
  const root = useAppSelector(isRoot);
  const admin = useAppSelector(isAdministrator);
  const [collapsed, setCollapsed] = useState(false);

  if (!isAuthenticated) {
    return <Navigate to={TO_SIGN_IN} replace />;
  }
  // 当前用户还在加载（load.ts 里 /auth/current 未返回）：先别判 403
  if (!user && !guest) {
    return <Spin fullscreen />;
  }
  if (!root && !admin) {
    return <Result status="403" title="403" subTitle={intl.formatMessage({ id: "admin.forbidden" })} />;
  }

  const t = (id: string) => intl.formatMessage({ id });
  const link = (path: string, id: string) => <Link to={path}>{t(id)}</Link>;
  const items: MenuProps["items"] = [
    {
      key: "stats",
      type: "group",
      label: t("admin.menu.stats"),
      children: [
        { key: "/admin/stats/reading", icon: <ReadOutlined />, label: link("/admin/stats/reading", "admin.menu.stats.reading") },
        { key: "/admin/stats/ai", icon: <RobotOutlined />, label: link("/admin/stats/ai", "admin.menu.stats.ai") },
        { key: "/admin/api", icon: <DashboardOutlined />, label: link("/admin/api", "admin.menu.api") },
      ],
    },
    {
      key: "manage",
      type: "group",
      label: t("admin.menu.manage"),
      children: [
        { key: "/admin/relation", icon: <ApartmentOutlined />, label: link("/admin/relation", "admin.menu.relation") },
        {
          key: "/admin/nissaya-ending",
          icon: <FontSizeOutlined />,
          label: link("/admin/nissaya-ending", "admin.menu.nissaya-ending"),
        },
        { key: "/admin/dictionary", icon: <BookOutlined />, label: link("/admin/dictionary", "admin.menu.dictionary") },
        {
          key: "/admin/dictionary/preference",
          icon: <SettingOutlined />,
          label: link("/admin/dictionary/preference", "admin.menu.dictionary.preference"),
        },
        { key: "/admin/users", icon: <TeamOutlined />, label: link("/admin/users", "admin.menu.users") },
        { key: "/admin/invite", icon: <MailOutlined />, label: link("/admin/invite", "admin.menu.invite") },
        { key: "/admin/ai-models", icon: <ApiOutlined />, label: link("/admin/ai-models", "admin.menu.ai-models") },
      ],
    },
  ];
  const selected = MENU_PATHS.find((path) => pathname === path || pathname.startsWith(`${path}/`));

  return (
    <Layout style={{ minHeight: "100vh" }}>
      <Sider
        style={{ backgroundColor: "unset" }}
        width={208}
        breakpoint="lg"
        collapsedWidth={0}
        trigger={null}
        collapsible
        collapsed={collapsed}
        onBreakpoint={(broken) => setCollapsed(broken)}
      >
        <div style={{ padding: "12px 16px", display: "flex", alignItems: "center", gap: 8 }}>
          <BarChartOutlined />
          <strong>{t("admin.title")}</strong>
        </div>
        <Menu mode="inline" selectedKeys={selected ? [selected] : []} items={items} style={{ borderInlineEnd: 0 }} />
      </Sider>
      <Layout>
        <div style={{ padding: "12px 16px", display: "flex", alignItems: "center", justifyContent: "space-between", gap: 8 }}>
          <Space>
            <Button
              type="text"
              icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />}
              onClick={() => setCollapsed(!collapsed)}
            />
            <HeaderBreadcrumb />
          </Space>
          <Space size="middle">
            <ThemeSwitch />
            <UiLangSelect />
            <SignInAvatar />
          </Space>
        </div>
        <Content style={{ padding: 12, minWidth: 0 }}>
          <Outlet />
        </Content>
      </Layout>
    </Layout>
  );
};

export default AdminLayout;
