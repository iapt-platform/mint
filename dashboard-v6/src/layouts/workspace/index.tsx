import { Button, Layout } from "antd";
import { Outlet } from "react-router";
import { useState } from "react";
import { MenuFoldOutlined, MenuUnfoldOutlined } from "@ant-design/icons";
import MainMenu from "../../components/navigation/MainMenu";
import SignInAvatar from "../../components/auth/SignInAvatar";

const { Sider, Content } = Layout;
const Widget = () => {
  const [collapsed, setCollapsed] = useState(false);
  return (
    <Layout style={{ minHeight: "100vh" }}>
      <Sider
        style={{ backgroundColor: "unset" }}
        trigger={null}
        collapsible
        collapsed={collapsed}
      >
        <div style={{ display: "flex", justifyContent: "space-between" }}>
          <SignInAvatar />
          <Button
            type="text"
            icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />}
            onClick={() => setCollapsed(!collapsed)}
          />
        </div>
        <MainMenu />
      </Sider>
      <Content>
        <Outlet />
      </Content>
    </Layout>
  );
};

export default Widget;
