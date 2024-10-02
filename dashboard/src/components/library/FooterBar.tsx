import { Link } from "react-router-dom";
import { Layout, Row, Col, Typography } from "antd";
import BeiAn from "../general/BeiAn";
import Feedback from "../general/Feedback";
import { useIntl } from "react-intl";

const { Footer } = Layout;
const { Paragraph } = Typography;

const FooterBarWidget = () => {
  const intl = useIntl();

  return (
    <Footer>
      <Row>
        <Col span={8}>
          <Paragraph strong>相关链接</Paragraph>
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
          <Paragraph strong>
            {intl.formatMessage({
              id: "labels.feedback",
            })}
          </Paragraph>
          <Feedback />
        </Col>
      </Row>
      <Row>
        <Col>
          <BeiAn />
        </Col>
      </Row>
    </Footer>
  );
};

export default FooterBarWidget;
