import { Affix, Space } from "antd";
import { Header } from "antd/lib/layout/layout";
import { useRef, useState } from "react";
import { useParams } from "react-router-dom";

import Article, { ArticleMode } from "../../../components/article/Article";
import ArticleCard from "../../../components/article/ArticleCard";
import MainMenu from "../../../components/article/MainMenu";
import ModeSwitch from "../../../components/article/ModeSwitch";
import RightPanel, { TPanelName } from "../../../components/article/RightPanel";
import RightToolsSwitch from "../../../components/article/RightToolsSwitch";
import ToolButtonSearch from "../../../components/article/ToolButtonSearch";
import ToolButtonSetting from "../../../components/article/ToolButtonSetting";
import ToolButtonTag from "../../../components/article/ToolButtonTag";
import ToolButtonToc from "../../../components/article/ToolButtonToc";

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
  console.log("mode", mode);
  const [articleMode, setArticleMode] = useState<ArticleMode>(
    mode as ArticleMode
  );
  const [rightPanel, setRightPanel] = useState<TPanelName>("close");

  const box = useRef<HTMLDivElement>(null);

  const openCol = () => {
    if (box.current) {
      box.current.style.display = "block";
    }
  };
  //const right = <ProTabs />;
  const rightBarWidth = "48px";
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
          <div>
            <MainMenu />
          </div>
          <div>tools</div>
          <div style={{ display: "flex" }}>
            <ModeSwitch
              onModeChange={(e: ArticleMode) => {
                setArticleMode(e);
              }}
            />
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
          <div style={{ height: `calc(100% - 44px)`, padding: 10 }}>
            <Space direction="vertical">
              <ToolButtonToc type={type} articleId={id} />
              <ToolButtonTag type={type} articleId={id} />
              <ToolButtonSearch type={type} articleId={id} />
              <ToolButtonSetting type={type} articleId={id} />
              <ToolButtonTag type={type} articleId={id} />
            </Space>
          </div>
        </Affix>
        <div
          style={{ width: `calc(100% - ${rightBarWidth})`, display: "flex" }}
        >
          <div style={{ flex: 5 }}>
            <ArticleCard
              type={type}
              articleId={id}
              onModeChange={(e: ArticleMode) => {
                setArticleMode(e);
              }}
              showCol={openCol}
            >
              <Article
                active={true}
                type={type}
                articleId={id}
                mode={articleMode}
              />
            </ArticleCard>
          </div>
          <div>
            <RightPanel curr={rightPanel} />
          </div>
        </div>
      </div>
    </div>
  );
};

export default Widget;
