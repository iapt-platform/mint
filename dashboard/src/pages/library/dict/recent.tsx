import { useNavigate } from "react-router-dom";
import { Layout, Affix, Col, Row } from "antd";
import { Input } from "antd";
import Dictionary from "../../../components/dict/Dictionary";

const { Content, Header } = Layout;
const { Search } = Input;

const Widget = () => {
  const navigate = useNavigate();

  return (
    <div>
      <Dictionary />
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
