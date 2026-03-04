import { useEffect, useState } from "react";
import { Divider, Space, Tag } from "antd";

import { get } from "../../request";
import type {
  ArticleMode,
  ArticleType,
  IArticleNavData,
  IArticleNavResponse,
} from "../../api/Article";

import "./article.css";

import ErrorResult from "../general/ErrorResult";

import TypeArticleReaderToolbar from "./components/ArticleReaderToolbar";
import type { TTarget } from "../../types";
import TocTree from "./components/TocTree";
import PaliText from "../general/PaliText";
import type { IFirstAnthology } from "./components/ArticleLayout";
import ArticleSkeleton from "./components/ArticleSkeleton";
import ArticleLayout from "./components/ArticleLayout";
import NavigateButton from "./components/NavigateButton";
import TocPath from "../tipitaka/TocPath";
import { useArticle } from "./hooks/useArticle";

interface IWidget {
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  parentChannels?: string[];
  anthologyId?: string | null;
  active?: boolean;
  hideInteractive?: boolean;
  hideTitle?: boolean;
  isSubWindow?: boolean;
  onArticleChange?: (type: ArticleType, id: string, target?: TTarget) => void;
  onAnthologySelect?: (
    id: string,
    e: React.MouseEvent<HTMLElement, MouseEvent>
  ) => void;
  onEdit?: () => void;
}
const ArticleReader = ({
  articleId,
  channelId,
  anthologyId,
  mode = "read",
  hideInteractive = false,
  hideTitle = false,
  isSubWindow = false,
  onArticleChange,
  onAnthologySelect,
  onEdit,
}: IWidget) => {
  const [nav, setNav] = useState<IArticleNavData>();
  const srcDataMode = mode === "edit" || mode === "wbw" ? "edit" : "read";

  const { data, loading, errorCode, refresh } = useArticle(articleId, {
    mode: srcDataMode,
    channelIds: channelId ? channelId?.split("_") : [],
  });

  const articleData = data;

  let articleHtml = ["<span />"];
  if (articleData?.html) {
    articleHtml = [articleData.html];
  } else if (articleData?.content) {
    articleHtml = [articleData.content];
  } else {
    articleHtml = [""];
  }

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
            onRefresh={refresh}
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
          <TocPath
            data={articleData?.path}
            channels={[]}
            onChange={(node, e) => {
              let newType: ArticleType = "article";
              if (node.level === 0) {
                newType = "anthology";
              }
              if (typeof onArticleChange !== "undefined") {
                if (node.key) {
                  const newArticleId = node.key;
                  const target = e.ctrlKey || e.metaKey ? "_blank" : "_self";
                  onArticleChange(newType, newArticleId, target);
                }
              }
            }}
          />
          <ArticleLayout
            title={title}
            subTitle={articleData?.subtitle}
            summary={articleData?.summary}
            content={articleData ? articleData.content : ""}
            html={articleHtml}
            created_at={articleData?.created_at}
            updated_at={articleData?.updated_at}
            anthology={anthology}
            hideTitle={hideTitle}
          />
          <Divider />
          <TocTree
            treeData={articleData?.toc?.map((item) => {
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
              const target = e.ctrlKey || e.metaKey ? "_blank" : "_self";
              if (typeof onArticleChange !== "undefined") {
                onArticleChange("article", id, target);
              }
            }}
          />
          <Divider />
          <NavigateButton
            prevTitle={nav?.prev?.title}
            nextTitle={nav?.next?.title}
            topOfChapter={topOfChapter}
            endOfChapter={endOfChapter}
            path={articleData?.path}
            onNext={() => {
              if (onArticleChange && nav?.next?.article_id) {
                onArticleChange("article", nav?.next?.article_id);
              }
            }}
            onPrev={() => {
              if (onArticleChange && nav?.prev?.article_id) {
                onArticleChange("article", nav?.prev?.article_id);
              }
            }}
            onPathChange={(key: string) => {
              if (typeof onArticleChange !== "undefined") {
                const node = articleData?.path?.find(
                  (value) => value.key === key
                );
                if (node) {
                  let newType: ArticleType = "article";
                  if (node.level === 0) {
                    newType = "anthology";
                  }
                  if (node.key) {
                    onArticleChange(newType, node.key, "_self");
                  }
                }
              }
            }}
          />
          {hideInteractive ? <></> : <></>}
        </>
      )}
    </div>
  );
  /**
   * <InteractiveArea resType={"article"} resId={articleId} />
   */
};

export default ArticleReader;
