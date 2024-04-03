import { Link } from "react-router-dom";
import type { MenuProps } from "antd";
import { Affix, Layout } from "antd";
import { Menu } from "antd";
import { AppstoreOutlined, HomeOutlined } from "@ant-design/icons";

const { Sider } = Layout;

const onClick: MenuProps["onClick"] = (e) => {
  console.log("click ", e);
};

type IWidgetHeadBar = {
  selectedKeys?: string;
};
const LeftSiderWidget = ({ selectedKeys = "" }: IWidgetHeadBar) => {
  const items: MenuProps["items"] = [
    {
      label: "API",
      key: "api",
      icon: <HomeOutlined />,
      children: [
        {
          label: <Link to="/admin/api/dashboard">dashboard</Link>,
          key: "dashboard",
        },
      ],
    },
    {
      label: "管理",
      key: "manage",
      icon: <HomeOutlined />,
      children: [
        {
          label: <Link to="/admin/relation/list">Relation</Link>,
          key: "relation",
        },
        {
          label: <Link to="/admin/nissaya-ending/list">Nissaya Ending</Link>,
          key: "nissaya-ending",
        },
        {
          label: <Link to="/admin/dictionary/list">Dictionary</Link>,
          key: "dict",
        },
        {
          label: <Link to="/admin/users/list">users</Link>,
          key: "users",
        },
        {
          label: <Link to="/admin/invite/list">invite</Link>,
          key: "invite",
        },
      ],
    },
    {
      label: "统计",
      key: "advance",
      icon: <AppstoreOutlined />,
      children: [],
    },
  ];

  return (
    <Affix offsetTop={0}>
      <Sider width={200} breakpoint="lg" className="site-layout-background">
        <Menu
          theme="light"
          onClick={onClick}
          defaultSelectedKeys={[selectedKeys]}
          defaultOpenKeys={["basic", "advance", "collaboration"]}
          mode="inline"
          items={items}
        />
      </Sider>
    </Affix>
  );
};

export default LeftSiderWidget;
