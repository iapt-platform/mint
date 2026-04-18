import { Typography, Divider, Skeleton, Space, Flex } from "antd";
import type { IStudio, IUser } from "../../../api/Auth";
import VisibleObserver from "../../general/VisibleObserver";
import MdView from "../../general/MdView";
import type { JSX, ReactNode } from "react";
import ArticleSkeleton from "./ArticleSkeleton";
import ErrorResult from "../../general/ErrorResult";
import User from "../../auth/User";

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
  title?: string;
  subTitle?: string;
  summary?: string | null;
  content?: string;
  html?: string[];
  nodes?: ReactNode[];
  resList?: JSX.Element;
  created_at?: string;
  updated_at?: string;
  editor?: IUser;
  owner?: IStudio;
  loading?: boolean;
  errorCode?: number;
  remains?: boolean;
  anthology?: IFirstAnthology;
  hideTitle?: boolean;
  onEnd?: () => void;
}

const ArticleLayout = ({
  title = "",
  subTitle,
  summary,
  content,
  html = [],
  nodes,
  editor,
  updated_at,
  resList,
  loading,
  errorCode,
  hideTitle,
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
          <Flex orientation="vertical" gap="middle">
            {hideTitle ? (
              <></>
            ) : (
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
            <Space>
              <User {...editor} /> edit at {updated_at}
            </Space>
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
