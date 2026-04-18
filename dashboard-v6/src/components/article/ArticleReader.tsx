import type React from "react";
import { Divider, Space, Tag } from "antd";

import type { ArticleMode, ArticleType } from "../../api/article";

import "./article.css";

import ErrorResult from "../general/ErrorResult";

import TypeArticleReaderToolbar from "./components/ArticleReaderToolbar";
import type { TTarget } from "../../types";
import TocTree from "./components/TocTree";
import PaliText from "../general/PaliText";
import type { IFirstAnthology } from "./components/ArticleLayout";
import ArticleSkeleton from "./components/ArticleSkeleton";
import ArticleLayout from "./components/ArticleLayout";
import ArticleNavigation from "./components/ArticleNavigation";
import TocPath from "../tipitaka/TocPath";
import { useArticle } from "./hooks/useArticle";
import ArticleHeader from "./components/ArticleHeader";

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
  headerExtra?: React.ReactNode;
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
  headerExtra,
  onArticleChange,
  onAnthologySelect,
  onEdit,
}: IWidget) => {
  const srcDataMode = mode === "edit" || mode === "wbw" ? "edit" : "read";

  const { data, loading, errorCode, refresh } = useArticle(articleId, {
    mode: srcDataMode,
    channelIds: channelId ? channelId?.split("_") : [],
    anthologyId: anthologyId,
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

  let anthology: IFirstAnthology | undefined;
  if (articleData?.anthology_count && articleData.anthology_first) {
    anthology = {
      id: articleData.anthology_first.uid,
      title: articleData.anthology_first.title,
      count: articleData?.anthology_count,
    };
  }

  const title = articleData?.title_text ?? articleData?.title;

  return (
    <div>
      {loading ? (
        <ArticleSkeleton />
      ) : errorCode ? (
        <ErrorResult code={errorCode} />
      ) : (
        <>
          <ArticleHeader
            header={
              <Space>
                {headerExtra}
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
                        const target =
                          e.ctrlKey || e.metaKey ? "_blank" : "_self";
                        onArticleChange(newType, newArticleId, target);
                      }
                    }
                  }}
                />
              </Space>
            }
            action={
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
            }
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
          <ArticleNavigation
            articleId={articleId}
            anthologyId={anthologyId}
            path={articleData?.path}
            onArticleChange={onArticleChange}
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
