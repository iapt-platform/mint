import { Link } from "react-router-dom";
import { Layout, Row, Col, Typography } from "antd";
import CreateFeedback from "../feedback/CreateFeedback";

const { Footer } = Layout;
const { Title } = Typography;

const FooterBarWidget = () => {
  //Library foot bar
  // TODO
  return (
    <Footer>
      <Row>
        <Col span={8}>
          <Title level={5}>相关链接</Title>
          <ul>
            <li>
              <Link to="www.github.com/iapt-platform/mint" target="_blank">
                wikipali in github
              </Link>
            </li>
            <li>nissaya project</li>
          </ul>
        </Col>
        <Col span={16}>
          <Title level={5}>问题反馈</Title>
        </Col>
      </Row>
      <Row>
        <Col>Powered by PCDS</Col>
      </Row>
    </Footer>
  );
};

export default FooterBarWidget;
