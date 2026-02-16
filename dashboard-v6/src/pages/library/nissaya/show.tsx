import { Col, Row } from "antd";
import { useParams } from "react-router-dom";
import NissayaCard from "../../../components/general/NissayaCard";

const Widget = () => {
  const { ending } = useParams(); //url 参数
  return (
    <Row>
      <Col flex={"auto"}></Col>
      <Col flex={"960px"}>
        <NissayaCard text={ending} cache={false} />
      </Col>
      <Col flex={"auto"}></Col>
    </Row>
  );
};

export default Widget;
