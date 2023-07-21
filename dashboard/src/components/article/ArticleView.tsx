import { Typography, Divider, Button, Skeleton } from "antd";
import { ReloadOutlined } from "@ant-design/icons";

import MdView from "../template/MdView";
import TocPath, { ITocPathNode } from "../corpus/TocPath";
import PaliChapterChannelList from "../corpus/PaliChapterChannelList";
import { ArticleType } from "./Article";
import VisibleObserver from "../general/VisibleObserver";

const { Paragraph, Title, Text } = Typography;

export interface IWidgetArticleData {
  id?: string;
  title?: string;
  subTitle?: string;
  summary?: string;
  content?: string;
  html?: string[];
  path?: ITocPathNode[];
  created_at?: string;
  updated_at?: string;
  channels?: string[];
  type?: ArticleType;
  articleId?: string;
  remains?: boolean;
  onEnd?: Function;
}

const ArticleViewWidget = ({
  id,
  title = "",
  subTitle,
  summary,
  content,
  html = [],
  path = [],
  created_at,
  updated_at,
  channels,
  type,
  articleId,
  onEnd,
  remains,
}: IWidgetArticleData) => {
  let currChannelList = <></>;
  switch (type) {
    case "chapter":
      const chapterProps = articleId?.split("-");
      if (typeof chapterProps === "object" && chapterProps.length > 0) {
        currChannelList = (
          <PaliChapterChannelList
            para={{
              book: parseInt(chapterProps[0]),
              para: parseInt(chapterProps[1]),
            }}
            channelId={channels}
            openTarget="_self"
          />
        );
      }

      break;

    default:
      break;
  }
  return (
    <>
      <div style={{ textAlign: "right" }}>
        <Button
          type="link"
          shape="round"
          size="small"
          icon={<ReloadOutlined />}
        />
      </div>

      <div>
        <TocPath data={path} channel={channels} />

        <Title level={4}>
          <div
            dangerouslySetInnerHTML={{
              __html: title ? title : "",
            }}
          />
        </Title>
        <Text type="secondary">{subTitle}</Text>
        {currChannelList}
        <Paragraph ellipsis={{ rows: 2, expandable: true, symbol: "more" }}>
          {summary}
        </Paragraph>
        <Divider />
      </div>
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
    </>
  );
};

export default ArticleViewWidget;
