import { Link } from "react-router-dom";
import { Row, Col, Progress, Space } from "antd";
import { Typography } from "antd";

import TimeShow from "../general/TimeShow";
import TocPath from "../corpus/TocPath";
import TagArea from "../tag/TagArea";
import type { IChannelApiData } from "../api/Channel";
import ChannelListItem from "../channel/ChannelListItem";
import { IStudio } from "../auth/StudioName";
import { ITagData } from "./ChapterTagList";

const { Title, Paragraph, Text } = Typography;

export interface ChapterData {
  title: string;
  paliTitle: string;
  path: string;
  book: number;
  paragraph: number;
  summary: string;
  tag: ITagData[];
  channel: IChannelApiData;
  studio: IStudio;
  progress: number;
  progressLine?: number[];
  createdAt: string;
  updatedAt: string;
  hit: number;
  like: number;
}

interface IWidgetChapterCard {
  data: ChapterData;
  onTagClick?: Function;
}

const Widget = ({ data, onTagClick }: IWidgetChapterCard) => {
  const path = JSON.parse(data.path);
  return (
    <>
      <Row>
        <Col>
          <Row>
            <Col span={16}>
              <Title level={5}>
                <Link
                  to={`/article/chapter/${data.book}-${data.paragraph}_${data.channel.id}`}
                  target="_blank"
                >
                  {data.title ? data.title : data.paliTitle}
                </Link>
              </Title>
              <Text type="secondary">{data.paliTitle}</Text>
              <TocPath data={path} />
            </Col>
            <Col span={8}>
              <Progress percent={data.progress} size="small" />
            </Col>
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
          <div style={{ display: "flex", justifyContent: "space-between" }}>
            <div>
              <TagArea
                data={data.tag}
                onTagClick={(tag: string) => {
                  if (typeof onTagClick !== "undefined") {
                    onTagClick(tag);
                  }
                }}
              />
            </div>
            <Space>
              <ChannelListItem channel={data.channel} studio={data.studio} />
              <TimeShow time={data.updatedAt} title="UpdatedAt" />
            </Space>
          </div>
        </Col>
      </Row>
    </>
  );
};

export default Widget;
