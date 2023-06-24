import { Typography, Divider, Button } from "antd";
import { ReloadOutlined } from "@ant-design/icons";

import MdView from "../template/MdView";
import TocPath, { ITocPathNode } from "../corpus/TocPath";
import PaliChapterChannelList from "../corpus/PaliChapterChannelList";
import { ArticleType } from "./Article";

const { Paragraph, Title, Text } = Typography;

export interface IWidgetArticleData {
  id?: string;
  title?: string;
  subTitle?: string;
  summary?: string;
  content?: string;
  html?: string;
  path?: ITocPathNode[];
  created_at?: string;
  updated_at?: string;
  channels?: string[];
  type?: ArticleType;
  articleId?: string;
}

const ArticleViewWidget = ({
  id,
  title = "",
  subTitle,
  summary,
  content,
  html,
  path = [],
  created_at,
  updated_at,
  channels,
  type,
  articleId,
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
      <div>
        <MdView html={html ? html : content} />
      </div>
    </>
  );
};

export default ArticleViewWidget;
