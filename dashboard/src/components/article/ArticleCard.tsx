import { Button, Card, Dropdown, Space, Segmented } from "antd";
import { MoreOutlined, ReloadOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import { IWidgetArticleData } from "./ArticleView";
import { useIntl } from "react-intl";
import { useState } from "react";
import ArticleCardMainMenu from "./ArticleCardMainMenu";
import store from "../../store";
import { modeChange } from "../../reducers/article-mode";
import { ArticleMode } from "./Article";

interface IWidgetArticleCard {
  type?: string;
  articleId?: string;
  data?: IWidgetArticleData;
  children?: React.ReactNode;
  onModeChange?: Function;
  openInCol?: Function;
  showCol?: Function;
}
const Widget = ({
  type,
  articleId,
  data,
  children,
  onModeChange,
  showCol,
}: IWidgetArticleCard) => {
  const intl = useIntl();
  const [mode, setMode] = useState<string>("read");

  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    switch (e.key) {
      case "showCol":
        if (typeof showCol !== "undefined") {
          showCol();
        }
        break;

      default:
        break;
    }
  };

  const items: MenuProps["items"] = [
    {
      key: "showCol",
      label: "显示分栏",
    },
  ];
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
        {
          label: intl.formatMessage({ id: "buttons.wbw" }),
          value: "wbw",
        },
      ]}
      value={mode}
      onChange={(value) => {
        if (typeof onModeChange !== "undefined") {
          if (mode === "read" || value.toString() === "read") {
            onModeChange(value.toString());
          }
        }
        setMode(value.toString());
        //发布mode变更
        store.dispatch(modeChange(value.toString() as ArticleMode));
      }}
    />
  );

  const contextMenu = (
    <Dropdown menu={{ items, onClick }} placement="bottomRight">
      <Button shape="circle" size="small" icon={<MoreOutlined />}></Button>
    </Dropdown>
  );
  return (
    <Card
      size="small"
      title={
        <Space>
          {<ArticleCardMainMenu type={type} articleId={articleId} />}
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
      bodyStyle={{ height: `calc(100vh - 94px)`, overflowY: "scroll" }}
    >
      {children}
    </Card>
  );
};

export default Widget;
