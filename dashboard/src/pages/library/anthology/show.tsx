import { useParams } from "react-router-dom";
import { Col, Row } from "antd";

import AnthologyDetail from "../../../components/article/AnthologyDetail";

const Widget = () => {
  // TODO
  const { id, tags } = useParams(); //url 参数

  const pageMaxWidth = "960px";
  return (
    <>
      <Row>
        <Col flex="auto"></Col>
        <Col flex={pageMaxWidth}>
          <AnthologyDetail aid={id} />
        </Col>
        <Col flex="auto"></Col>
      </Row>
    </>
  );
};

export default Widget;
