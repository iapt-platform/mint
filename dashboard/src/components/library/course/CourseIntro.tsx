//课程详情简介
import { Col, Row } from "antd";
import { marked } from "marked";

interface IWidget {
  intro?: string;
}
const Widget = ({ intro }: IWidget) => {
  return (
    <Row>
      <Col flex="auto"></Col>
      <Col flex="960px">
        <div
          dangerouslySetInnerHTML={{
            __html: marked.parse(intro ? intro : ""),
          }}
        />
      </Col>
      <Col flex="auto"></Col>
    </Row>
  );
};

export default Widget;
