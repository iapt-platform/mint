import { Outlet } from "react-router-dom";

import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";
import { Col, Row } from "antd";

const Widget = () => {
  // TODO
  return (
    <div>
      <HeadBar selectedKeys="discussion" />
      <Row>
        <Col flex={"auto"}></Col>
        <Col flex={"960px"}>
          <Outlet />
        </Col>
        <Col flex={"auto"}></Col>
      </Row>
      <FooterBar />
    </div>
  );
};

export default Widget;
