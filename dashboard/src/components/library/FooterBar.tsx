import { Link } from "react-router-dom";
import { Layout, Row, Col } from "antd";
import CreateFeedback from "../feedback/CreateFeedback";

const { Footer } = Layout;

const Widget = () => {
  //Library foot bar
  // TODO
  return (
    <Footer>
      <Row>
        <Col span={8}>
          <h3>相关链接</h3>
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
          <h3>问题反馈</h3>
          <div>
            <CreateFeedback />
          </div>
        </Col>
      </Row>
      <Row>
        <Col>Powered by PCDS</Col>
      </Row>
    </Footer>
  );
};

export default Widget;
