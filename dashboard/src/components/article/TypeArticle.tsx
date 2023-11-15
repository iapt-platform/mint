import { useEffect, useState } from "react";
import { Divider, message, Space, Tag } from "antd";

import { get } from "../../request";
import { IArticleDataResponse, IArticleResponse } from "../api/Article";
import ArticleView, { IFirstAnthology } from "./ArticleView";
import TocTree from "./TocTree";
import PaliText from "../template/Wbw/PaliText";
import { ITocPathNode } from "../corpus/TocPath";
import { ArticleMode, ArticleType } from "./Article";
import "./article.css";
import ArticleSkeleton from "./ArticleSkeleton";
import ErrorResult from "../general/ErrorResult";
import AnthologiesAtArticle from "./AnthologiesAtArticle";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  anthologyId?: string | null;
  active?: boolean;
  onArticleChange?: Function;
  onFinal?: Function;
  onLoad?: Function;
  onAnthologySelect?: Function;
}
const TypeArticleWidget = ({
  type,
  channelId,
  articleId,
  anthologyId,
  mode = "read",
  active = false,
  onArticleChange,
  onFinal,
  onLoad,
  onAnthologySelect,
}: IWidget) => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [articleHtml, setArticleHtml] = useState<string[]>(["<span />"]);
  const [extra, setExtra] = useState(<></>);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number>();
  const [currPath, setCurrPath] = useState<ITocPathNode[]>();

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
    console.log("url", url);
    setLoading(true);
    console.log("url", url);
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
              onSelect={(keys: string[]) => {
                console.log(keys);
                if (typeof onArticleChange !== "undefined" && keys.length > 0) {
                  onArticleChange("article", keys[0]);
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

  let anthology: IFirstAnthology | undefined;
  if (articleData?.anthology_count && articleData.anthology_first) {
    anthology = {
      id: articleData.anthology_first.uid,
      title: articleData.anthology_first.title,
      count: articleData?.anthology_count,
    };
  }

  return (
    <div>
      {loading ? (
        <ArticleSkeleton />
      ) : errorCode ? (
        <ErrorResult code={errorCode} />
      ) : (
        <>
          <AnthologiesAtArticle
            articleId={articleId}
            anthologyId={anthologyId}
            onClick={(
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
            title={
              articleData?.title_text
                ? articleData?.title_text
                : articleData?.title
            }
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
            onAnthologySelect={(id: string) => {
              if (typeof onAnthologySelect !== "undefined") {
                onAnthologySelect(id);
              }
            }}
          />
          <Divider />
          {extra}
          <Divider />
        </>
      )}
    </div>
  );
};

export default TypeArticleWidget;
