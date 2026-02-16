import { useNavigate } from "react-router-dom";
import { Layout, Col, Row } from "antd";
import Dictionary from "../../../components/dict/Dictionary";

const { Content } = Layout;

const Widget = () => {
  const navigate = useNavigate();

  return (
    <div>
      <Dictionary
        onSearch={(value: string) => {
          navigate("/dict/" + value);
        }}
      />
      <Content>
        <Row>
          <Col flex="auto"></Col>
          <Col flex="1260px">最近搜索列表</Col>
          <Col flex="auto"></Col>
        </Row>
      </Content>
    </div>
  );
};

export default Widget;
