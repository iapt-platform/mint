import { Outlet } from "react-router-dom";
import { Layout, Row, Col } from "antd";

import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";

const Widget = () => {
  return (
    <Layout>
      <HeadBar selectedKeys="community" />
      <Row>
        <Col flex="auto"></Col>

        <Col flex="1260px">
          <Outlet />
        </Col>
        <Col flex="auto"></Col>
      </Row>
      <FooterBar />
    </Layout>
  );
};

export default Widget;
