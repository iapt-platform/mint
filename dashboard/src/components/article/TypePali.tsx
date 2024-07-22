import { useEffect, useState } from "react";
import { Divider, message, Space, Tag } from "antd";

import { get, post } from "../../request";
import {
  IArticleDataResponse,
  IArticleResponse,
  IChapterToc,
} from "../api/Article";
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
import Navigate from "./Navigate";
import { ISearchParams } from "../../pages/library/article/show";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  book?: string | null;
  para?: string | null;
  active?: boolean;
  focus?: string | null;
  hideNav?: boolean;
  onArticleChange?: (
    type: ArticleType,
    id: string,
    target: string,
    param?: ISearchParams[]
  ) => void;
  onFinal?: Function;
  onLoad?: Function;
  onTitle?: Function;
}
const TypePaliWidget = ({
  type,
  book,
  para,
  channelId,
  articleId,
  mode = "read",
  active = true,
  hideNav = false,
  focus,
  onArticleChange,
  onFinal,
  onLoad,
  onTitle,
}: IWidget) => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [articleHtml, setArticleHtml] = useState<string[]>(["<span />"]);
  const [toc, setToc] = useState<IChapterToc[]>();
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number>();

  const [remains, setRemains] = useState(false);

  const channels = channelId?.split("_");

  const srcDataMode = mode === "edit" || mode === "wbw" ? "edit" : "read";

  useEffect(() => {
    const parts = focus?.split("-");
    if (parts?.length === 2) {
      store.dispatch(refresh({ type: "para", id: focus }));
    } else if (parts?.length === 4) {
      store.dispatch(refresh({ type: "sentence", id: focus }));
    }
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
    console.info("TypePali api request", url);
    get<IArticleResponse>(url)
      .then((json) => {
        console.debug("TypePali api response ", json);
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

          setToc(json.data.toc);

          switch (type) {
            case "chapter":
              if (typeof articleId === "string") {
                const [book, para] = articleId.split("-");
                if (channelId) {
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
              }
              break;
            default:
              break;
          }

          if (typeof onLoad !== "undefined") {
            onLoad(json.data);
          }
          if (typeof onTitle !== "undefined") {
            const bookTitle =
              json.data.path && json.data.path.length > 0
                ? json.data.path[0].title
                : "";
            onTitle(`${bookTitle}-${json.data.title}`);
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
      }
    });
    return;
  };

  let title = "";
  if (articleData) {
    if (type === "chapter") {
      title = articleData.title_text
        ? articleData.title_text
        : articleData.title;
    } else {
      const id = articleId?.split("-");
      title = id ? (id.length > 1 ? id[1] : "unknown") : "unknown";
    }
  }

  let fullPath: ITocPathNode[] = [];
  if (articleData && articleData.path && articleData.path.length > 0) {
    if (typeof articleId === "string") {
      const [book, para] = articleId.split("-");
      const currNode: ITocPathNode = {
        book: parseInt(book),
        paragraph: parseInt(para),
        title: title ?? "",
        level: articleData.path[articleData.path.length - 1].level + 1,
      };
      fullPath = [...articleData.path, currNode];
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
          <ArticleView
            id={articleData?.uid}
            title={title}
            subTitle={articleData?.subtitle}
            summary={articleData?.summary}
            content={articleData ? articleData.content : ""}
            html={articleHtml}
            path={fullPath}
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
              let newArticle = "";
              if (node.level === 0) {
                newType = "series";
                newArticle = node.title;
              } else {
                newType = "chapter";
                newArticle = node.key
                  ? node.key
                  : `${node.book}-${node.paragraph}`;
              }

              if (typeof onArticleChange !== "undefined") {
                const target = e.ctrlKey || e.metaKey ? "_blank" : "self";
                onArticleChange(newType, newArticle, target);
              }
            }}
          />
          <Divider />
          <TocTree
            treeData={toc?.map((item) => {
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
                    <PaliText text={strTitle === "" ? "[unnamed]" : strTitle} />
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
              if (typeof onArticleChange !== "undefined") {
                if (e.ctrlKey || e.metaKey) {
                  onArticleChange("chapter", id, "_blank");
                } else {
                  onArticleChange("chapter", id, "_self");
                }
              }
            }}
          />
          <Divider />
          {hideNav ? (
            <></>
          ) : (
            <Navigate
              type={type as ArticleType}
              articleId={articleId}
              path={fullPath}
              onPathChange={(key: string) => {
                const node = articleData?.path?.find(
                  (value) => value.title === key
                );
                if (node) {
                  let newType = type;
                  if (node.level === 0) {
                    newType = "series";
                  } else {
                    newType = "chapter";
                  }
                  if (typeof onArticleChange !== "undefined") {
                    const newArticle = node.key
                      ? node.key
                      : `${node.book}-${node.paragraph}`;
                    onArticleChange(newType, newArticle, "self");
                  }
                }
              }}
              onChange={(
                event: React.MouseEvent<HTMLElement, MouseEvent>,
                newId: string
              ) => {
                let target: string = "_self";
                if (event.ctrlKey || event.metaKey) {
                  target = "_blank";
                }
                let param: ISearchParams[] = [];
                if (type === "para" && newId) {
                  if (newId.split("-").length > 1) {
                    param = [{ key: "par", value: newId.split("-")[1] }];
                  }
                }
                if (typeof onArticleChange !== "undefined") {
                  onArticleChange(type as ArticleType, newId, target, param);
                }
              }}
            />
          )}
        </>
      )}
    </div>
  );
};

export default TypePaliWidget;
