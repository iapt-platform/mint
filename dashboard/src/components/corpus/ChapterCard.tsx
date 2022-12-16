import { Link } from "react-router-dom";
import { Row, Col } from "antd";
import { Typography } from "antd";

import TimeShow from "../general/TimeShow";
import TocPath from "../corpus/TocPath";
import TagArea from "../tag/TagArea";
import type { TagNode } from "../tag/TagArea";
import type { ChannelInfoProps } from "../api/Channel";
import ChannelListItem from "../channel/ChannelListItem";

const { Title, Paragraph, Text } = Typography;

export interface ChapterData {
  title: string;
  paliTitle: string;
  path: string;
  book: number;
  paragraph: number;
  summary: string;
  tag: TagNode[];
  channel: ChannelInfoProps;
  createdAt: string;
  updatedAt: string;
  hit: number;
  like: number;
}

interface IWidgetChapterCard {
  data: ChapterData;
}

const Widget = ({ data }: IWidgetChapterCard) => {
  const path = JSON.parse(data.path);
  const tags = data.tag;
  return (
    <>
      <Row>
        <Col>
          <Row>
            <Col span={16}>
              <Title level={5}>
                <Link
                  to={`/article/chapter/${data.book}-${data.paragraph}_${data.channel.channelId}`}
                  target="_blank"
                >
                  {data.title}
                </Link>
              </Title>
              <Text type="secondary">{data.paliTitle}</Text>
              <TocPath data={path} />
            </Col>
            <Col span={8}>进度条</Col>
          </Row>
          <Row>
            <Col>
              <Paragraph
                ellipsis={{
                  rows: 2,
                  expandable: false,
                  symbol: "more",
                }}
              >
                {data.summary}
              </Paragraph>
            </Col>
          </Row>
          <Row>
            <Col span={16}>
              <TagArea data={tags} />
            </Col>
            <Col span={5}>
              <ChannelListItem data={data.channel} />
            </Col>
            <Col span={3}>
              <TimeShow time={data.updatedAt} title="UpdatedAt" />
            </Col>
          </Row>
        </Col>
      </Row>
    </>
  );
};

export default Widget;
