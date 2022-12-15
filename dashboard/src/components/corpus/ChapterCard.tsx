import { Row, Col } from "antd";
import { Typography } from "antd";

import TimeShow from "../general/TimeShow";
import TocPath from "../corpus/TocPath";
import TagArea from "../tag/TagArea";
import type { TagNode } from "../tag/TagArea";
import type { ChannelInfoProps } from "../api/Channel";
import ChannelListItem from "../channel/ChannelListItem";

const { Title, Paragraph, Link, Text } = Typography;

export interface ChapterData {
  Title: string;
  PaliTitle: string;
  Path: string;
  Book: number;
  Paragraph: number;
  Summary: string;
  Tag: TagNode[];
  Channel: ChannelInfoProps;
  CreatedAt: string;
  UpdatedAt: string;
  Hit: number;
  Like: number;
}

interface IWidgetChapterCard {
  data: ChapterData;
}

const Widget = ({ data }: IWidgetChapterCard) => {
  const path = JSON.parse(data.Path);
  const tags = data.Tag;
  return (
    <>
      <Row>
        <Col>
          <Row>
            <Col span={16}>
              <Title level={5}>
                <Link>{data.Title}</Link>
              </Title>
              <Text type="secondary">{data.PaliTitle}</Text>
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
                {data.Summary}
              </Paragraph>
            </Col>
          </Row>
          <Row>
            <Col span={16}>
              <TagArea data={tags} />
            </Col>
            <Col span={5}>
              <ChannelListItem data={data.Channel} />
            </Col>
            <Col span={3}>
              <TimeShow time={data.UpdatedAt} title="UpdatedAt" />
            </Col>
          </Row>
        </Col>
      </Row>
    </>
  );
};

export default Widget;
