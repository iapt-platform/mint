//课程详情简介
import { Col, Row, Typography } from "antd";

import Marked from "../general/Marked";
const { Paragraph } = Typography;
interface IWidget {
  intro?: string;
}
const CourseIntroWidget = ({ intro }: IWidget) => {
  return (
    <Row>
      <Col flex="auto"></Col>
      <Col flex="960px">
        <Paragraph>
          <Marked text={intro} />
        </Paragraph>
      </Col>
      <Col flex="auto"></Col>
    </Row>
  );
};

export default CourseIntroWidget;
