import { Link } from "react-router-dom";
import { Row, Col } from "antd";
import { Card } from "antd";
import { Typography } from "antd";

import StudioName from "../auth/Studio";
import type { IStudio } from "../auth/Studio";
import type { ListNodeData } from "./EditableTree";

const { Title, Text } = Typography;

export interface IArticleData {
  id: string;
  title: string;
  subTitle: string;
  summary: string;
  created_at: string;
  updated_at: string;
}

export interface IAnthologyData {
  id: string;
  title: string;
  subTitle: string;
  summary: string;
  articles: ListNodeData[];
  studio: IStudio;
  created_at: string;
  updated_at: string;
}

interface IWidgetAnthologyCard {
  data: IAnthologyData;
}

const AnthologyCardWidget = (prop: IWidgetAnthologyCard) => {
  const articleList = prop.data.articles.map((item, id) => {
    return <div key={id}>{item.title}</div>;
  });
  return (
    <>
      <Card
        hoverable
        bordered={false}
        style={{ width: "100%", borderRadius: 8 }}
      >
        <Title level={4}>
          <Link to={`/anthology/${prop.data.id}`}>{prop.data.title}</Link>
        </Title>
        <div>
          <Text type="secondary">{prop.data.subTitle}</Text>
        </div>
        <div>
          <Text>{prop.data.summary}</Text>
        </div>
        <Link to={`/blog/${prop.data.studio.studioName}/anthology`}>
          <StudioName data={prop.data.studio} />
        </Link>
        <Row>
          <Col flex={"100px"}>Content</Col>
          <Col flex={"auto"}>{articleList}</Col>
        </Row>
      </Card>
    </>
  );
};

export default AnthologyCardWidget;
