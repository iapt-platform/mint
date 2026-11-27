import { Typography, Divider, Skeleton, Flex } from "antd";
import type { IStudio } from "../../../api/Auth";
import VisibleObserver from "../../general/VisibleObserver";
import MdView from "../../general/MdView";
import type { JSX, ReactNode } from "react";
import ArticleSkeleton from "./ArticleSkeleton";
import ErrorResult from "../../general/ErrorResult";
import EditorsLine, { type IEditorBrief } from "../../auth/EditorsLine";

const { Paragraph, Title, Text } = Typography;

export interface IPath {
  title: JSX.Element | string;
}
export interface IFirstAnthology {
  id: string;
  title: string;
  count: number;
}
export interface IArticleLayout {
  /** 显示在标题上方（如术语的质量标签） */
  preTitle?: ReactNode;
  title?: string;
  subTitle?: string;
  summary?: string | null;
  content?: string;
  html?: string[];
  nodes?: ReactNode[];
  resList?: JSX.Element;
  created_at?: string;
  updated_at?: string;
  editor?: IEditorBrief | null;
  /** 协作者（v3 的 co_editor）：人类授权代写的 AI 模型 */
  coEditor?: IEditorBrief | null;
  /** 显示在编辑者那一行下面（如术语所属的 channel） */
  afterEditors?: ReactNode;
  owner?: IStudio;
  loading?: boolean;
  errorCode?: number;
  remains?: boolean;
  anthology?: IFirstAnthology;
  hideTitle?: boolean;
  hideHead?: boolean;
  onEnd?: () => void;
}

const ArticleLayout = ({
  preTitle,
  title = "",
  subTitle,
  summary,
  content,
  html = [],
  nodes,
  editor,
  coEditor,
  updated_at,
  afterEditors,
  resList,
  loading,
  errorCode,
  hideTitle = false,
  hideHead = false,
  remains,
  onEnd,
}: IArticleLayout) => {
  console.log("ArticleViewWidget render");

  return (
    <>
      {loading ? (
        <ArticleSkeleton />
      ) : errorCode ? (
        <ErrorResult code={errorCode} />
      ) : (
        <div>
          <Flex
            style={{ display: hideHead ? "none" : "block" }}
            orientation="vertical"
            gap="middle"
          >
            {preTitle}
            {!hideTitle && (
              <Title level={4}>
                <div
                  dangerouslySetInnerHTML={{
                    __html: title ?? "",
                  }}
                />
              </Title>
            )}

            <Text type="secondary">{subTitle}</Text>
            {resList}
            <Paragraph ellipsis={{ rows: 2, expandable: true, symbol: "more" }}>
              {summary}
            </Paragraph>
            <EditorsLine
              editor={editor}
              coEditor={coEditor}
              updatedAt={updated_at}
            />
            {afterEditors}
            <Divider />
          </Flex>
          {html
            ? html.map((item, id) => {
                return (
                  <div key={id}>
                    <MdView className="pcd_article" html={item} />
                  </div>
                );
              })
            : content}
          {nodes}
          {remains ? (
            <>
              <VisibleObserver
                onVisible={(visible: boolean) => {
                  console.log("visible", visible);
                  if (visible && typeof onEnd !== "undefined") {
                    onEnd();
                  }
                }}
              />
              <Skeleton title={{ width: 200 }} paragraph={{ rows: 5 }} active />
            </>
          ) : undefined}
        </div>
      )}
    </>
  );
};

export default ArticleLayout;
