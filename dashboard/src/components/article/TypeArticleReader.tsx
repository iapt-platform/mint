import { useEffect, useState } from "react";
import { Divider, message, Space, Tag } from "antd";

import { get } from "../../request";
import {
  IArticleDataResponse,
  IArticleNavData,
  IArticleNavResponse,
  IArticleResponse,
} from "../api/Article";
import ArticleView, { IFirstAnthology } from "./ArticleView";
import TocTree from "./TocTree";
import PaliText from "../template/Wbw/PaliText";
import { ITocPathNode } from "../corpus/TocPath";
import { ArticleMode, ArticleType } from "./Article";
import "./article.css";
import ArticleSkeleton from "./ArticleSkeleton";
import ErrorResult from "../general/ErrorResult";
import NavigateButton from "./NavigateButton";
import InteractiveArea from "../discussion/InteractiveArea";
import TypeArticleReaderToolbar from "./TypeArticleReaderToolbar";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  anthologyId?: string | null;
  active?: boolean;
  hideInteractive?: boolean;
  hideTitle?: boolean;
  isSubWindow?: boolean;
  onArticleChange?: Function;
  onLoad?: Function;
  onAnthologySelect?: Function;
  onEdit?: Function;
}
const TypeArticleReaderWidget = ({
  type,
  channelId,
  articleId,
  anthologyId,
  mode = "read",
  active = false,
  hideInteractive = false,
  hideTitle = false,
  isSubWindow = false,
  onArticleChange,
  onLoad,
  onAnthologySelect,
  onEdit,
}: IWidget) => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [articleHtml, setArticleHtml] = useState<string[]>(["<span />"]);
  const [extra, setExtra] = useState(<></>);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number>();
  const [currPath, setCurrPath] = useState<ITocPathNode[]>();
  const [nav, setNav] = useState<IArticleNavData>();

  const channels = channelId?.split("_");

  const srcDataMode = mode === "edit" || mode === "wbw" ? "edit" : "read";
  useEffect(() => {
    console.log("srcDataMode", srcDataMode);
    if (!active) {
      return;
    }

    if (typeof type === "undefined") {
      return;
    }

    let url = `/v2/article/${articleId}?mode=${srcDataMode}`;
    url += channelId ? `&channel=${channelId}` : "";
    url += anthologyId ? `&anthology=${anthologyId}` : "";
    console.info("article url", url);
    setLoading(true);
    get<IArticleResponse>(url)
      .then((json) => {
        console.log("article", json);
        if (json.ok) {
          setArticleData(json.data);
          setCurrPath(json.data.path);
          if (json.data.html) {
            setArticleHtml([json.data.html]);
          } else if (json.data.content) {
            setArticleHtml([json.data.content]);
          } else {
            setArticleHtml([""]);
          }
          setExtra(
            <TocTree
              treeData={json.data.toc?.map((item) => {
                const strTitle = item.title ? item.title : item.pali_title;
                const key = item.key
                  ? item.key
                  : `${item.book}-${item.paragraph}`;
                const progress = item.progress?.map((item, id) => (
                  <Tag key={id}>{Math.round(item * 100) + "%"}</Tag>
                ));
                return {
                  key: key,
                  title: (
                    <Space>
                      <PaliText
                        text={strTitle === "" ? "[unnamed]" : strTitle}
                      />
                      {progress}
                    </Space>
                  ),
                  level: item.level,
                };
              })}
              onClick={(
                id: string,
                e: React.MouseEvent<HTMLSpanElement, MouseEvent>
              ) => {
                const target = e.ctrlKey || e.metaKey ? "_blank" : "self";
                if (typeof onArticleChange !== "undefined") {
                  onArticleChange("article", id, target);
                }
              }}
            />
          );

          if (typeof onLoad !== "undefined") {
            onLoad(json.data);
          }
        } else {
          console.error("json", json);
          message.error(json.message);
        }
      })
      .finally(() => {
        setLoading(false);
      })
      .catch((e) => {
        console.error(e);
        setErrorCode(e);
      });
  }, [active, type, articleId, srcDataMode, channelId, anthologyId]);

  useEffect(() => {
    const url = `/v2/nav-article/${articleId}_${anthologyId}`;
    console.info("api request", url);
    get<IArticleNavResponse>(url)
      .then((json) => {
        console.debug("api response", json);
        if (json.ok) {
          setNav(json.data);
        }
      })
      .catch((e) => {
        console.error(e);
      });
  }, [anthologyId, articleId]);

  let anthology: IFirstAnthology | undefined;
  if (articleData?.anthology_count && articleData.anthology_first) {
    anthology = {
      id: articleData.anthology_first.uid,
      title: articleData.anthology_first.title,
      count: articleData?.anthology_count,
    };
  }

  const title = articleData?.title_text ?? articleData?.title;

  let endOfChapter = false;
  if (nav?.curr && nav?.next) {
    if (nav?.curr?.level > nav?.next?.level) {
      endOfChapter = true;
    }
  }

  let topOfChapter = false;
  if (nav?.curr && nav?.prev) {
    if (nav?.curr?.level > nav?.prev?.level) {
      topOfChapter = true;
    }
  }

  return (
    <div>
      {loading ? (
        <ArticleSkeleton />
      ) : errorCode ? (
        <ErrorResult code={errorCode} />
      ) : (
        <>
          <TypeArticleReaderToolbar
            title={title}
            articleId={articleId}
            anthologyId={anthologyId}
            role={articleData?.role}
            isSubWindow={isSubWindow}
            onEdit={() => {
              if (typeof onEdit !== "undefined") {
                onEdit();
              }
            }}
            onAnthologySelect={(
              id: string,
              e: React.MouseEvent<HTMLElement, MouseEvent>
            ) => {
              if (typeof onAnthologySelect !== "undefined") {
                onAnthologySelect(id, e);
              }
            }}
          />
          <ArticleView
            id={articleData?.uid}
            title={title}
            subTitle={articleData?.subtitle}
            summary={articleData?.summary}
            content={articleData ? articleData.content : ""}
            html={articleHtml}
            path={currPath}
            created_at={articleData?.created_at}
            updated_at={articleData?.updated_at}
            channels={channels}
            type={type}
            articleId={articleId}
            anthology={anthology}
            hideTitle={hideTitle}
            onPathChange={(
              node: ITocPathNode,
              e: React.MouseEvent<
                HTMLSpanElement | HTMLAnchorElement,
                MouseEvent
              >
            ) => {
              let newType = type;
              if (node.level === 0) {
                newType = "anthology";
              } else {
                newType = "article";
              }
              if (typeof onArticleChange !== "undefined") {
                const newArticleId = node.key;
                const target = e.ctrlKey || e.metaKey ? "_blank" : "self";
                onArticleChange(newType, newArticleId, target);
              }
            }}
          />
          <Divider />
          {extra}
          <Divider />
          <NavigateButton
            prevTitle={nav?.prev?.title}
            nextTitle={nav?.next?.title}
            topOfChapter={topOfChapter}
            endOfChapter={endOfChapter}
            path={currPath}
            onNext={() => {
              if (typeof onArticleChange !== "undefined") {
                onArticleChange("article", nav?.next?.article_id);
              }
            }}
            onPrev={() => {
              if (typeof onArticleChange !== "undefined") {
                onArticleChange("article", nav?.prev?.article_id);
              }
            }}
            onPathChange={(key: string) => {
              if (typeof onArticleChange !== "undefined") {
                const node = currPath?.find((value) => value.key === key);
                if (node) {
                  let newType = type;
                  if (node.level === 0) {
                    newType = "anthology";
                  } else {
                    newType = "article";
                  }
                  onArticleChange(newType, node.key, "_self");
                }
              }
            }}
          />
          {hideInteractive ? (
            <></>
          ) : (
            <InteractiveArea resType={"article"} resId={articleId} />
          )}
        </>
      )}
    </div>
  );
};

export default TypeArticleReaderWidget;
