import { Affix, Divider, Space } from "antd";
import { Header } from "antd/lib/layout/layout";
import { useState } from "react";
import { useNavigate, useParams } from "react-router-dom";

import Article, {
  ArticleMode,
  ArticleType,
} from "../../../components/article/Article";

import MainMenu from "../../../components/article/MainMenu";
import ModeSwitch from "../../../components/article/ModeSwitch";
import RightPanel, { TPanelName } from "../../../components/article/RightPanel";
import RightToolsSwitch from "../../../components/article/RightToolsSwitch";
import ToolButtonSearch from "../../../components/article/ToolButtonSearch";
import ToolButtonSetting from "../../../components/article/ToolButtonSetting";
import ToolButtonTag from "../../../components/article/ToolButtonTag";
import ToolButtonToc from "../../../components/article/ToolButtonToc";
import Avatar from "../../../components/auth/Avatar";
import { IChannel } from "../../../components/channel/Channel";

/**
 * type:
 *   sent 句子
 *   sim  相似句
 *   v_para vri 自然段
 *   page  页码
 *   chapter 段落
 *   article 文章
 *   textbook 课本
 * @returns
 */
const Widget = () => {
  const { type, id, mode = "read" } = useParams(); //url 参数
  const navigate = useNavigate();
  console.log("mode", mode);
  const [articleMode, setArticleMode] = useState<ArticleMode>(
    mode as ArticleMode
  );
  const [rightPanel, setRightPanel] = useState<TPanelName>("close");

  //const right = <ProTabs />;
  const rightBarWidth = "48px";
  const channelId = id?.split("_").slice(1);
  return (
    <div>
      <Affix offsetTop={0}>
        <Header
          style={{
            height: 44,
            lineHeight: 44,
            display: "flex",
            justifyContent: "space-between",
            padding: "5px",
          }}
        >
          <MainMenu />
          <div></div>
          <div key="right" style={{ display: "flex" }}>
            <ModeSwitch
              onModeChange={(e: ArticleMode) => {
                setArticleMode(e);
              }}
            />
            <Divider type="vertical" />
            <RightToolsSwitch
              onModeChange={(open: TPanelName) => {
                setRightPanel(open);
              }}
            />
          </div>
        </Header>
      </Affix>
      <div style={{ width: "100%", display: "flex" }}>
        <Affix offsetTop={44}>
          <div
            style={{
              height: `calc(100% - 44px)`,
              padding: 8,
              display: "flex",
              flexDirection: "column",
              justifyContent: "space-between",
            }}
          >
            <div>
              <Space direction="vertical">
                <ToolButtonToc type={type} articleId={id} />
                <ToolButtonTag type={type} articleId={id} />
                <ToolButtonSearch type={type} articleId={id} />
                <ToolButtonSetting type={type} articleId={id} />
              </Space>
            </div>
            <div>
              <Space direction="vertical">
                <Avatar placement="rightBottom" />
              </Space>
            </div>
          </div>
        </Affix>
        <div
          key="main"
          style={{ width: `calc(100% - ${rightBarWidth})`, display: "flex" }}
        >
          <div
            key="Article"
            style={{ marginLeft: "auto", marginRight: "auto", width: 960 }}
          >
            <Article
              active={true}
              type={type as ArticleType}
              articleId={id}
              mode={articleMode}
              onArticleChange={(article: string) => {
                console.log("article change", article);
                navigate(`/article/${type}/${article}/${articleMode}`);
              }}
            />
          </div>
          <div key="RightPanel">
            <RightPanel
              curr={rightPanel}
              type={type as ArticleType}
              articleId={id ? id : ""}
              selectedChannelKeys={channelId}
              onChannelSelect={(e: IChannel[]) => {
                const oldId = id?.split("_");
                const newId = [
                  oldId ? oldId[0] : undefined,
                  ...e.map((item) => item.id),
                ];
                navigate(`/article/${type}/${newId.join("_")}/${articleMode}`);
              }}
            />
          </div>
        </div>
      </div>
    </div>
  );
};

export default Widget;
