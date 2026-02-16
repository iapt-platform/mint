import { Outlet } from "react-router-dom";

import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";
import { Col, Layout, Row } from "antd";

const Widget = () => {
  return (
    <Layout>
      <HeadBar selectedKeys="discussion" />
      <Row>
        <Col flex={"auto"}></Col>
        <Col flex={"960px"}>
          <Outlet />
        </Col>
        <Col flex={"auto"}></Col>
      </Row>
      <FooterBar />
    </Layout>
  );
};

export default Widget;
