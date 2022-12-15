import { useState } from "react";
import { useParams } from "react-router-dom";
import { useNavigate } from "react-router-dom";
import { Layout, Affix, Col, Row } from "antd";
import { Input } from "antd";

import DictSearch from "../../../components/dict/DictSearch";

const { Content } = Layout;
const { Search } = Input;

const Widget = () => {
  const navigate = useNavigate();
  const { word } = useParams(); //url 参数
  const [wordSearch, setWordSearch] = useState(word);

  const onSearch = (value: string) => {
    console.log("onSearch", value);
    setWordSearch(value);
    navigate("/dict/" + value);
  };
  return (
    <>
      <Layout>
        <Affix offsetTop={0}>
          <div style={{ backgroundColor: "gray", height: "3.5em" }}>
            <Row style={{ paddingTop: "0.5em" }}>
              <Col span="8" offset={8}>
                <Search
                  placeholder="input search text"
                  onSearch={onSearch}
                  style={{ width: "100%" }}
                />
              </Col>
            </Row>
          </div>
        </Affix>
        <Content>
          <Row>
            <Col flex="auto"></Col>
            <Col flex="1260px">
              <DictSearch word={wordSearch} />
            </Col>
            <Col flex="auto"></Col>
          </Row>
        </Content>
      </Layout>
    </>
  );
};

export default Widget;
