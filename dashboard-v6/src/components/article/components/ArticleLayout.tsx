import { Typography, Divider, Skeleton, Space } from "antd";
import type { ITocPathNode } from "../../../api/pali-text";
import type { IStudio, IUser } from "../../../api/Auth";
import TocPath from "../../tipitaka/TocPath";
import VisibleObserver from "../../general/VisibleObserver";
import MdView from "../../general/MdView";
import type { JSX } from "react";
import ArticleSkeleton from "./ArticleSkeleton";
import ErrorResult from "../../general/ErrorResult";
import User from "../../auth/User";

const { Paragraph, Title, Text } = Typography;
export interface IFirstAnthology {
  id: string;
  title: string;
  count: number;
}
export interface IWidgetArticleData {
  title?: string;
  subTitle?: string;
  summary?: string | null;
  content?: string;
  html?: string[];
  path?: ITocPathNode[];
  resList?: JSX.Element;
  created_at?: string;
  updated_at?: string;
  editor?: IUser;
  owner?: IStudio;
  channels?: string[];
  loading?: boolean;
  errorCode?: number;
  remains?: boolean;
  anthology?: IFirstAnthology;
  hideTitle?: boolean;
  onEnd?: () => void;
  onPathChange?: (
    node: ITocPathNode,
    e: React.MouseEvent<HTMLSpanElement | HTMLAnchorElement, MouseEvent>
  ) => void;
}

const ArticleLayout = ({
  title = "",
  subTitle,
  summary,
  content,
  html = [],
  path = [],
  editor,
  updated_at,
  resList,
  channels,
  loading,
  errorCode,
  hideTitle,
  remains,
  onEnd,
  onPathChange,
}: IWidgetArticleData) => {
  console.log("ArticleViewWidget render");

  return (
    <>
      {loading ? (
        <ArticleSkeleton />
      ) : errorCode ? (
        <ErrorResult code={errorCode} />
      ) : (
        <div>
          <Space orientation="vertical">
            {hideTitle ? (
              <></>
            ) : (
              <TocPath
                data={path}
                channels={channels}
                onChange={(
                  node: ITocPathNode,
                  e: React.MouseEvent<
                    HTMLSpanElement | HTMLAnchorElement,
                    MouseEvent
                  >
                ) => {
                  if (typeof onPathChange !== "undefined") {
                    onPathChange(node, e);
                  }
                }}
              />
            )}
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
          </Space>
          {html
            ? html.map((item, id) => {
                return (
                  <div key={id}>
                    <MdView className="pcd_article" html={item} />
                  </div>
                );
              })
            : content}
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
