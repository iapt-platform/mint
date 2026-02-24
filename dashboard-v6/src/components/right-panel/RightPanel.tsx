import React from "react";
import { AndroidOutlined, AppleOutlined } from "@ant-design/icons";
import { Tabs, type TabsProps } from "antd";

const RightPanel: React.FC = () => {
  const items: TabsProps["items"] = [
    {
      key: "1",
      label: "",
      children: "Content of Tab Pane 1",
      icon: <AndroidOutlined />,
    },
    {
      key: "2",
      label: "",
      icon: <AppleOutlined />,
      children: "Content of Tab Pane 2",
    },
    {
      key: "3",
      label: "",
      icon: <AndroidOutlined />,
      children: "Content of Tab Pane 3",
    },
  ];
  return <Tabs defaultActiveKey="2" tabPlacement="end" items={items} />;
};

export default RightPanel;
