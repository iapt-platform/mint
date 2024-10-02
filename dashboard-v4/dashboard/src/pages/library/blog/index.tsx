import { Outlet } from "react-router-dom";
import { Col, Layout, Row } from "antd";

import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";

const Widget = () => {
  return (
    <Layout>
      <HeadBar />
      <div>cover</div>
      <Layout>
        <Row>
          <Col flex="auto"></Col>
          <Col flex="1260px">
            <Outlet />
          </Col>
          <Col flex="auto"></Col>
        </Row>
      </Layout>

      <FooterBar />
    </Layout>
  );
};

export default Widget;
