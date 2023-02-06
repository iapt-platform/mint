import { useParams } from "react-router-dom";
import { Layout, Affix, Col, Row } from "antd";

import AnthologyDetail from "../../../components/article/AnthologyDetail";

const { Content, Header } = Layout;

const Widget = () => {
  // TODO
  const { id, tags } = useParams(); //url 参数
  let channel = tags ? tags : "";

  const pageMaxWidth = "960px";
  return (
    <>
      <Row>
        <Col flex="auto"></Col>
        <Col flex={pageMaxWidth}>
          <Row>
            <Col span="18">
              <AnthologyDetail aid={id} />
            </Col>
            <Col span="6"></Col>
          </Row>
        </Col>
        <Col flex="auto"></Col>
      </Row>
    </>
  );
};

export default Widget;
