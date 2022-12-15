import { useParams } from "react-router-dom";
import { Layout, Affix, Col, Row } from "antd";

import AnthologyDetail from "../../../components/article/AnthologyDetail";

const { Content, Header } = Layout;

const Widget = () => {
  // TODO
  const { id, tags } = useParams(); //url 参数
  let aid = id ? id : "";
  let channel = tags ? tags : "";

  const pageMaxWidth = "1260px";
  return (
    <Layout>
      <Affix offsetTop={0}>
        <Header style={{ backgroundColor: "gray", height: "3.5em" }}>
          <Col flex="auto"></Col>
          <Col flex={pageMaxWidth}>
            <div>
              {aid}@{channel}
            </div>
          </Col>
          <Col flex="auto"></Col>
        </Header>
      </Affix>

      <Content>
        <Row>
          <Col flex="auto"></Col>
          <Col flex={pageMaxWidth}>
            <Row>
              <Col span="18">
                <AnthologyDetail aid={aid} />
              </Col>
              <Col span="6"></Col>
            </Row>
          </Col>
          <Col flex="auto"></Col>
        </Row>
      </Content>
    </Layout>
  );
};

export default Widget;
