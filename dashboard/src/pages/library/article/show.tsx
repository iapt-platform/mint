import { Affix, Button, Divider, Space, Tooltip } from "antd";
import { Header } from "antd/lib/layout/layout";
import { Key } from "antd/lib/table/interface";
import { useEffect, useState } from "react";
import { useNavigate, useParams, useSearchParams } from "react-router-dom";
import { FieldTimeOutlined, UnorderedListOutlined } from "@ant-design/icons";

import { ColumnOutlinedIcon } from "../../../assets/icon";
import { IArticleDataResponse } from "../../../components/api/Article";
import { IApiResponseDictList } from "../../../components/api/Dict";
import AnchorNav from "../../../components/article/AnchorNav";

import Article, {
  ArticleMode,
  ArticleType,
} from "../../../components/article/Article";

import MainMenu from "../../../components/article/MainMenu";
import ModeSwitch from "../../../components/article/ModeSwitch";
import Navigate from "../../../components/article/Navigate";
import RightPanel, { TPanelName } from "../../../components/article/RightPanel";
import ToolButtonDiscussion from "../../../components/article/ToolButtonDiscussion";
import ToolButtonNav from "../../../components/article/ToolButtonNav";
import ToolButtonPr from "../../../components/article/ToolButtonPr";
import ToolButtonSearch from "../../../components/article/ToolButtonSearch";
import ToolButtonSetting from "../../../components/article/ToolButtonSetting";
import ToolButtonTag from "../../../components/article/ToolButtonTag";
import ToolButtonToc from "../../../components/article/ToolButtonToc";
import Avatar from "../../../components/auth/Avatar";
import { IChannel } from "../../../components/channel/Channel";
import NetStatus from "../../../components/general/NetStatus";
import RecentModal from "../../../components/recent/RecentModal";
import { useAppSelector } from "../../../hooks";
import { add } from "../../../reducers/inline-dict";
import { paraParam } from "../../../reducers/para-change";
import { get } from "../../../request";
import store from "../../../store";
import { IRecent } from "../../../components/recent/RecentList";
import { fullUrl } from "../../../utils";
import ThemeSelect from "../../../components/general/ThemeSelect";

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
  const [anchorNavOpen, setAnchorNavOpen] = useState(false);
  const [recentModalOpen, setRecentModalOpen] = useState(false);
  const [loadedArticleData, setLoadedArticleData] =
    useState<IArticleDataResponse>();

  const paraChange = useAppSelector(paraParam);

  useEffect(() => {
    if (typeof paraChange === "undefined") {
      return;
    }
    let newType: string = paraChange?.type;
    let newId: string;
    switch (paraChange?.type) {
      case "chapter":
        newId = `${paraChange.book}-${paraChange.para}`;
        break;
      case "para":
        newId = `${paraChange.book}-${paraChange.para}`;
        break;
      default:
        newId = "";
        break;
    }
    let url = `/article/${newType}/${newId}?`;
    let param: string[] = [];
    searchParams.forEach((value, key) => {
      if (key !== "book" && key !== "par") {
        param.push(`${key}=${value}`);
      }
    });
    param.push(`book=${paraChange.book}`);
    param.push(`par=${paraChange.para}`);
    navigate(url + param.join("&"));
  }, [paraChange]);

  useEffect(() => {
    /**
     * 启动时载入格位公式字典
     */
    //TODO 存储到session storage
    const url = `/v2/userdict?view=dict&id=2142c229-8860-4ca5-a82e-1afc7e4f1e5d`;
    console.log("url", url);
    get<IApiResponseDictList>(url).then((json) => {
      console.log("_formula_ ok", json.data.count);
      //存储到redux
      store.dispatch(add(json.data.rows));
    });
  }, []);
  const rightBarWidth = "48px";

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
          <div style={{ display: "flex" }}>
            <MainMenu />
            <NetStatus style={{ color: "white" }} />
          </div>
          <div></div>
          <Space key="right">
            {type === "article" && loadedArticleData ? (
              <Button
                ghost
                onClick={() => {
                  navigate(
                    `/studio/${loadedArticleData.studio?.realName}/article/${loadedArticleData.uid}/edit`
                  );
                }}
              >
                Edit
              </Button>
            ) : undefined}
            <Avatar placement="bottom" />
            <ThemeSelect />
            <Divider type="vertical" />
            <ModeSwitch
              channel={searchParams.get("channel")}
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
              onChannelChange={(channels: IChannel[], mode: ArticleMode) => {
                let output: any = {
                  mode: mode,
                  channel: channels.map((item) => item.id).join("_"),
                };
                searchParams.forEach((value, key) => {
                  console.log(value, key);
                  if (key !== "mode" && key !== "channel") {
                    output[key] = value;
                  }
                });
                setSearchParams(output);
              }}
            />
            <Tooltip title="文章目录" placement="bottomLeft">
              <Button
                style={{ display: "block", color: "white" }}
                icon={<UnorderedListOutlined />}
                type="text"
                onClick={() => setAnchorNavOpen((value) => !value)}
              />
            </Tooltip>
            <Tooltip title="侧边栏" placement="bottomLeft">
              <Button
                style={{ display: "block", color: "white" }}
                icon={<ColumnOutlinedIcon />}
                type="text"
                onClick={() =>
                  setRightPanel((value) =>
                    value === "close" ? "open" : "close"
                  )
                }
              />
            </Tooltip>
          </Space>
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
                <RecentModal
                  trigger={<Button icon={<FieldTimeOutlined />} />}
                  open={recentModalOpen}
                  onOpen={(isOpen: boolean) => setRecentModalOpen(isOpen)}
                  onSelect={(
                    event: React.MouseEvent<HTMLElement, MouseEvent>,
                    param: IRecent
                  ) => {
                    setRecentModalOpen(false);
                    let url = `/article/${param.type}/${param.articleId}?mode=`;
                    url += param.param?.mode ? param.param?.mode : "read";
                    url += param.param?.channel
                      ? `&channel=${param.param?.channel}`
                      : "";
                    url += param.param?.book
                      ? `&book=${param.param?.book}`
                      : "";
                    url += param.param?.para ? `&par=${param.param?.para}` : "";
                    if (event.ctrlKey || event.metaKey) {
                      window.open(fullUrl(url), "_blank");
                    } else {
                      navigate(url);
                    }
                  }}
                />
                <ToolButtonToc
                  type={type as ArticleType}
                  articleId={id}
                  anthologyId={searchParams.get("anthology")}
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
                <ToolButtonNav type={type} articleId={id} />
                <ToolButtonTag type={type} articleId={id} />
                <ToolButtonPr type={type} articleId={id} />
                <ToolButtonDiscussion type={type} articleId={id} />
                <ToolButtonSearch type={type} articleId={id} />
                <ToolButtonSetting type={type} articleId={id} />
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
            style={{ marginLeft: "auto", marginRight: "auto", width: 1100 }}
          >
            <Article
              active={true}
              type={type as ArticleType}
              book={searchParams.get("book")}
              para={searchParams.get("par")}
              channelId={searchParams.get("channel")}
              articleId={id}
              anthologyId={searchParams.get("anthology")}
              mode={searchParams.get("mode") as ArticleMode}
              onArticleChange={(article: string, target?: string) => {
                console.log("article change", article, target);
                let mType = type;
                if (article.split("-").length === 2) {
                  mType = "chapter";
                }
                let url = `/article/${mType}/${article}?mode=${currMode}`;
                searchParams.forEach((value, key) => {
                  console.log(value, key);
                  if (key !== "mode") {
                    url += `&${key}=${value}`;
                  }
                });
                if (target === "_blank") {
                  window.open(fullUrl(url), "_blank");
                } else {
                  navigate(url);
                }
              }}
              onLoad={(article: IArticleDataResponse) => {
                setLoadedArticleData(article);
              }}
              onAnthologySelect={(id: string) => {
                let output: any = { anthology: id };
                searchParams.forEach((value, key) => {
                  console.log(value, key);
                  if (key !== "anthology") {
                    output[key] = value;
                  }
                });
                setSearchParams(output);
              }}
            />
            <Navigate
              type={type as ArticleType}
              articleId={id}
              onChange={(
                event: React.MouseEvent<HTMLElement, MouseEvent>,
                newId: string
              ) => {
                let url = `/article/${type}/${newId}?mode=${currMode}`;
                searchParams.forEach((value, key) => {
                  if (key !== "mode" && key !== "par") {
                    url += `&${key}=${value}`;
                  }
                });
                if (type === "para") {
                  url += "&par=" + newId.split("-")[1];
                }
                if (event.ctrlKey || event.metaKey) {
                  window.open(fullUrl(url), "_blank");
                } else {
                  navigate(url);
                }
              }}
            />
          </div>
          <div key="RightPanel">
            <AnchorNav open={anchorNavOpen} />
            <RightPanel
              curr={rightPanel}
              type={type as ArticleType}
              articleId={id ? id : ""}
              selectedChannelsId={searchParams.get("channel")?.split("_")}
              onClose={() => {
                setRightPanel("close");
              }}
              onTabChange={(curr: TPanelName) => setRightPanel(curr)}
              onChannelSelect={(e: IChannel[]) => {
                //channel 改变
                console.log("onChannelSelect", e);
                const channels = e.map((item) => item.id).join("_");
                console.log("channels", channels);
                let url = `/article/${type}/${id}?mode=${currMode}`;
                searchParams.forEach((value, key) => {
                  console.log(value, key);
                  if (key !== "mode" && key !== "channel") {
                    url += `&${key}=${value}`;
                  }
                });
                if (e.length > 0) {
                  url += `&channel=${channels}`;
                }
                console.log("url", url);
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
