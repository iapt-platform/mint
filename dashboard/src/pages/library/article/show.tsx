import { Affix, Divider, Space } from "antd";
import { Header } from "antd/lib/layout/layout";
import { Key } from "antd/lib/table/interface";
import { useEffect, useState } from "react";
import { useNavigate, useParams, useSearchParams } from "react-router-dom";
import { IApiResponseDictList } from "../../../components/api/Dict";

import Article, {
  ArticleMode,
  ArticleType,
} from "../../../components/article/Article";

import MainMenu from "../../../components/article/MainMenu";
import ModeSwitch from "../../../components/article/ModeSwitch";
import RightPanel, { TPanelName } from "../../../components/article/RightPanel";
import RightToolsSwitch from "../../../components/article/RightToolsSwitch";
import ToolButtonDiscussion from "../../../components/article/ToolButtonDiscussion";
import ToolButtonPr from "../../../components/article/ToolButtonPr";
import ToolButtonSearch from "../../../components/article/ToolButtonSearch";
import ToolButtonSetting from "../../../components/article/ToolButtonSetting";
import ToolButtonTag from "../../../components/article/ToolButtonTag";
import ToolButtonToc from "../../../components/article/ToolButtonToc";
import Avatar from "../../../components/auth/Avatar";
import { IChannel } from "../../../components/channel/Channel";
import { add } from "../../../reducers/inline-dict";
import { get } from "../../../request";
import store from "../../../store";

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
  const [rightPanel, setRightPanel] = useState<TPanelName>("close");
  const [searchParams, setSearchParams] = useSearchParams();

  useEffect(() => {
    /**
     * 启动时载入格位公式字典
     */
    get<IApiResponseDictList>(`/v2/userdict?view=word&word=_formula_`).then(
      (json) => {
        console.log("_formula_ ok", json.data.count);
        //存储到redux
        store.dispatch(add(json.data.rows));
      }
    );
  }, []);
  const rightBarWidth = "48px";
  const channelId = id?.split("_").slice(1);
  let currMode: ArticleMode;
  if (searchParams.get("mode") !== null) {
    currMode = searchParams.get("mode") as ArticleMode;
  } else {
    currMode = "read";
  }
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
              currMode={currMode}
              onModeChange={(e: ArticleMode) => {
                let output: any = { mode: e };
                searchParams.forEach((value, key) => {
                  console.log(value, key);
                  if (key !== "mode") {
                    output[key] = value;
                  }
                });
                setSearchParams(output);
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
                <ToolButtonToc
                  type={type as ArticleType}
                  articleId={id}
                  onSelect={(key: Key) => {
                    console.log("toc click", key);
                    let url = `/article/${type}/${key}?`;
                    let param: string[] = [];
                    searchParams.forEach((value, key) => {
                      param.push(`${key}=${value}`);
                    });
                    navigate(url + param.join("&"));
                  }}
                />
                <ToolButtonTag type={type} articleId={id} />
                <ToolButtonPr type={type} articleId={id} />
                <ToolButtonDiscussion type={type} articleId={id} />
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
            style={{ marginLeft: "auto", marginRight: "auto", width: 1200 }}
          >
            <Article
              active={true}
              type={type as ArticleType}
              book={searchParams.get("book")}
              para={searchParams.get("par")}
              channelId={searchParams.get("channel")}
              articleId={id}
              mode={searchParams.get("mode") as ArticleMode}
              onArticleChange={(article: string) => {
                console.log("article change", article);
                let url = `/article/${type}/${article}?mode=${currMode}`;
                searchParams.forEach((value, key) => {
                  console.log(value, key);
                  if (key !== "mode") {
                    url += `&${key}=${value}`;
                  }
                });
                navigate(url);
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
                //channel 改变
                console.log("onChannelSelect", e);
                const channels = e.map((item) => item.id).join("_");
                let url = `/article/${type}/${id}?mode=${currMode}`;
                searchParams.forEach((value, key) => {
                  console.log(value, key);
                  if (key !== "mode") {
                    if (key === "channel") {
                      if (e.length > 0) {
                        url += `&channel=${channels}`;
                      }
                    } else {
                      url += `&${key}=${value}`;
                    }
                  }
                });

                navigate(url);
              }}
            />
          </div>
        </div>
      </div>
    </div>
  );
};

export default Widget;
