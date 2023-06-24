import { useNavigate } from "react-router-dom";
import { Button, Card, Dropdown, Space } from "antd";
import { MoreOutlined, ReloadOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";

import { IWidgetArticleData } from "./ArticleView";
import ArticleCardMainMenu from "./ArticleCardMainMenu";
import ModeSwitch from "./ModeSwitch";

interface IWidgetArticleCard {
  type?: string;
  articleId?: string;
  data?: IWidgetArticleData;
  children?: React.ReactNode;
  onModeChange?: Function;
  openInCol?: Function;
  showCol?: Function;
}
const ArticleCardWidget = ({
  type,
  articleId,
  data,
  children,
  onModeChange,
  showCol,
}: IWidgetArticleCard) => {
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
          <ModeSwitch
            channel={null}
            onModeChange={(mode: string) => {
              if (typeof onModeChange !== "undefined") {
                onModeChange(mode);
              }
              navigate(`/article/${type}/${articleId}/${mode}`);
            }}
          />
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

export default ArticleCardWidget;
