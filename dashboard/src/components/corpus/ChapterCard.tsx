import { Link } from "react-router-dom";
import { Row, Col, Progress, Space } from "antd";
import { Typography } from "antd";

import TimeShow from "../general/TimeShow";
import TocPath from "../corpus/TocPath";
import TagArea from "../tag/TagAreaInChapter";
import type { IChannelApiData } from "../api/Channel";
import ChannelListItem from "../channel/ChannelListItem";
import { IStudio } from "../auth/Studio";
import { ITagData } from "./ChapterTag";

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

interface IWidget {
  data: ChapterData;
  onTagClick?: Function;
}

const ChapterCardWidget = ({ data, onTagClick }: IWidget) => {
  const path = JSON.parse(data.path);
  let url = `/article/chapter/${data.book}-${data.paragraph}`;
  url += data.channel.id ? `?channel=${data.channel.id}` : "";
  return (
    <Row>
      <Col>
        <Row>
          <Col span={16}>
            <Title level={5}>
              <Link to={url} target="_blank">
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
        <div
          style={{
            display: "flex",
            flexWrap: "wrap",
            justifyContent: "space-between",
          }}
        >
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
          <Space
            style={{
              flexWrap: "wrap",
              justifyContent: "flex-end",
              marginLeft: "auto",
              fontSize: 12,
            }}
          >
            <ChannelListItem channel={data.channel} studio={data.studio} />
            <TimeShow updatedAt={data.updatedAt} />
          </Space>
        </div>
      </Col>
    </Row>
  );
};

export default ChapterCardWidget;
