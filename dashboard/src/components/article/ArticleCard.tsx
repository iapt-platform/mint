import { Button, Card, Dropdown, Menu, Space, Segmented } from "antd";
import { MoreOutlined, MenuOutlined, ReloadOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import ArticleView, { IWidgetArticleData } from "./ArticleView";
import { useIntl } from "react-intl";
import { useState } from "react";

interface IWidgetArticleCard {
  data?: IWidgetArticleData;
  showModeSwitch?: boolean;
  showMainMenu?: boolean;
  showContextMenu?: boolean;
  showResTab?: boolean;
  children?: React.ReactNode;
  onModeChange?: Function;
  openInCol?: Function;
}
const Widget = ({
  data,
  showModeSwitch = true,
  showMainMenu = true,
  showContextMenu = true,
  showResTab = true,
  children,
  onModeChange,
  openInCol,
}: IWidgetArticleCard) => {
  const intl = useIntl();
  const [mode, setMode] = useState<string>("read");

  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
  };

  const menu = (
    <Menu
      onClick={onClick}
      items={[
        {
          key: "close",
          label: "关闭",
        },
      ]}
    />
  );
  const modeSwitch = (
    <Segmented
      size="middle"
      options={[
        {
          label: intl.formatMessage({ id: "buttons.read" }),
          value: "read",
        },
        {
          label: intl.formatMessage({ id: "buttons.edit" }),
          value: "edit",
        },
      ]}
      value={mode}
      onChange={(value) => {
        if (typeof onModeChange !== "undefined") {
          onModeChange(value.toString());
        }
        setMode(value.toString());
      }}
    />
  );
  const mainMenu = <Button size="small" icon={<MenuOutlined />} />;
  const contextMenu = (
    <Dropdown overlay={menu} placement="bottomRight">
      <Button shape="circle" size="small" icon={<MoreOutlined />}></Button>
    </Dropdown>
  );
  return (
    <Card
      size="small"
      title={
        <Space>
          {mainMenu}
          {data?.title}
        </Space>
      }
      extra={
        <Space>
          {modeSwitch}
          <Button
            shape="circle"
            size="small"
            icon={<ReloadOutlined />}
          ></Button>
          {contextMenu}
        </Space>
      }
      bodyStyle={{ height: 630, overflowY: "scroll" }}
    >
      {children}
    </Card>
  );
};

export default Widget;
