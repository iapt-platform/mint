import { useNavigate } from "react-router-dom";
import { useIntl } from "react-intl";
import { useState } from "react";
import { Button, Card, Dropdown, Space, Segmented } from "antd";
import { MoreOutlined, ReloadOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";

import store from "../../store";
import { modeChange } from "../../reducers/article-mode";
import { IWidgetArticleData } from "./ArticleView";
import ArticleCardMainMenu from "./ArticleCardMainMenu";
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
  const navigate = useNavigate();

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
          label: intl.formatMessage({ id: "buttons.translate" }),
          value: "edit",
        },
        {
          label: intl.formatMessage({ id: "buttons.wbw" }),
          value: "wbw",
        },
      ]}
      value={mode}
      onChange={(value) => {
        const newMode = value.toString();
        if (typeof onModeChange !== "undefined") {
          if (mode === "read" || newMode === "read") {
            onModeChange(newMode);
          }
        }
        setMode(newMode);
        //发布mode变更
        store.dispatch(modeChange(newMode as ArticleMode));
        //修改url
        navigate(`/article/${type}/${articleId}/${newMode}`);
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
