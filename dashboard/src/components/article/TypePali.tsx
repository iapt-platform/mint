import { useEffect, useState } from "react";
import { Divider, message, Result, Space, Tag } from "antd";

import { get, post } from "../../request";
import { IArticleDataResponse, IArticleResponse } from "../api/Article";
import ArticleView from "./ArticleView";
import TocTree from "./TocTree";
import PaliText from "../template/Wbw/PaliText";
import { IViewRequest, IViewStoreResponse } from "../api/view";
import { ITocPathNode } from "../corpus/TocPath";
import { ArticleMode, ArticleType } from "./Article";
import "./article.css";
import ArticleSkeleton from "./ArticleSkeleton";
import ErrorResult from "../general/ErrorResult";
import store from "../../store";
import { refresh } from "../../reducers/focus";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  book?: string | null;
  para?: string | null;
  active?: boolean;
  focus?: string | null;
  onArticleChange?: Function;
  onFinal?: Function;
  onLoad?: Function;
}
const TypePaliWidget = ({
  type,
  book,
  para,
  channelId,
  articleId,
  mode = "read",
  active = true,
  focus,
  onArticleChange,
  onFinal,
  onLoad,
}: IWidget) => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [articleHtml, setArticleHtml] = useState<string[]>(["<span />"]);
  const [extra, setExtra] = useState(<></>);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number>();

  const [remains, setRemains] = useState(false);

  const channels = channelId?.split("_");

  const srcDataMode = mode === "edit" || mode === "wbw" ? "edit" : "read";

  useEffect(() => {
    store.dispatch(refresh({ type: "para", id: focus }));
  }, [focus]);

  useEffect(() => {
    console.log("srcDataMode", srcDataMode);
    if (!active) {
      return;
    }
    if (typeof type === "undefined") {
      return;
    }

    let url = "";
    switch (type) {
      case "chapter":
        if (typeof articleId !== "undefined") {
          url = `/v2/corpus-chapter/${articleId}?mode=${srcDataMode}`;
          url += channelId ? `&channels=${channelId}` : "";
        }
        break;
      case "para":
        const _book = book ? book : articleId;
        url = `/v2/corpus?view=para&book=${_book}&par=${para}&mode=${srcDataMode}`;
        url += channelId ? `&channels=${channelId}` : "";
        break;
      default:
        if (typeof articleId !== "undefined") {
          url = `/v2/corpus/${type}/${articleId}/${srcDataMode}?mode=${srcDataMode}`;
          url += channelId ? `&channel=${channelId}` : "";
        }
        break;
    }

    setLoading(true);
    console.log("url", url);
    get<IArticleResponse>(url)
      .then((json) => {
        console.log("article", json);
        if (json.ok) {
          setArticleData(json.data);
          if (json.data.html) {
            setArticleHtml([json.data.html]);
          } else if (json.data.content) {
            setArticleHtml([json.data.content]);
          }
          if (json.data.from) {
            setRemains(true);
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
                  onArticleChange(keys[0]);
                }
              }}
            />
          );

          switch (type) {
            case "chapter":
              if (typeof articleId === "string" && channelId) {
                const [book, para] = articleId?.split("-");
                post<IViewRequest, IViewStoreResponse>("/v2/view", {
                  target_type: type,
                  book: parseInt(book),
                  para: parseInt(para),
                  channel: channelId,
                  mode: srcDataMode,
                }).then((json) => {
                  console.log("view", json.data);
                });
              }
              break;
            default:
              break;
          }

          if (typeof onLoad !== "undefined") {
            onLoad(json.data);
          }
        } else {
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
  }, [active, type, articleId, srcDataMode, book, para, channelId]);

  const getNextPara = (next: IArticleDataResponse): void => {
    if (
      typeof next.paraId === "undefined" ||
      typeof next.mode === "undefined" ||
      typeof next.from === "undefined" ||
      typeof next.to === "undefined"
    ) {
      setRemains(false);
      return;
    }
    let url = `/v2/corpus-chapter/${next.paraId}?mode=${next.mode}`;
    url += `&from=${next.from}`;
    url += `&to=${next.to}`;
    url += channels ? `&channels=${channels}` : "";
    console.log("lazy load", url);
    get<IArticleResponse>(url).then((json) => {
      if (json.ok) {
        if (typeof json.data.content === "string") {
          const content: string = json.data.content;
          setArticleData((origin) => {
            if (origin) {
              origin.from = json.data.from;
            }
            return origin;
          });
          setArticleHtml((origin) => {
            return [...origin, content];
          });
        }

        //getNextPara(json.data);
      }
    });
    return;
  };

  return (
    <div>
      {loading ? (
        <ArticleSkeleton />
      ) : errorCode ? (
        <ErrorResult code={errorCode} />
      ) : (
        <>
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
            path={articleData?.path}
            created_at={articleData?.created_at}
            updated_at={articleData?.updated_at}
            channels={channels}
            type={type}
            articleId={articleId}
            remains={remains}
            onEnd={() => {
              if (type === "chapter" && articleData) {
                getNextPara(articleData);
              }
            }}
            onPathChange={(
              node: ITocPathNode,
              e: React.MouseEvent<
                HTMLSpanElement | HTMLAnchorElement,
                MouseEvent
              >
            ) => {
              let newType = type;
              if (node.level === 0) {
                switch (type) {
                  case "article":
                    newType = "anthology";
                    break;
                  case "chapter":
                    newType = "series";
                    break;
                  default:
                    break;
                }
              }

              if (typeof onArticleChange !== "undefined") {
                const newArticle = node.key
                  ? node.key
                  : `${node.book}-${node.paragraph}`;
                const target = e.ctrlKey || e.metaKey ? "_blank" : "self";
                onArticleChange(newType, newArticle, target);
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

export default TypePaliWidget;
