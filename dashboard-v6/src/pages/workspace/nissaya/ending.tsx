import { Col, Row } from "antd";
import { useParams } from "react-router";

import NissayaCardWidget from "../../../components/nissaya/NissayaCard";

const Widget = () => {
  const { ending } = useParams();

  return (
    <>
      <title>{ending}</title>
      <Row>
        <Col flex="auto" />
        <Col flex="960px">
          <NissayaCardWidget text={ending} />
        </Col>
        <Col flex="auto" />
      </Row>
    </>
  );
};

export default Widget;
