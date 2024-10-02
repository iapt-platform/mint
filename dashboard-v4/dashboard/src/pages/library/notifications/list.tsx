import { Col, Row } from "antd";
import NotificationList from "../../../components/notification/NotificationList";

const Widget = () => {
  // TODO i18n
  return (
    <Row>
      <Col flex="auto"></Col>
      <Col flex="960px">
        <NotificationList />
      </Col>
      <Col flex="auto"></Col>
    </Row>
  );
};

export default Widget;
