import { Layout, Affix, Col, Row } from "antd";
import { Input } from "antd";

import { useState } from "react";
import DictSearch from "./DictSearch";
const { Search } = Input;

const Widget = () => {
  const [container, setContainer] = useState<HTMLDivElement | null>(null);
  const [wordSearch, setWordSearch] = useState("");

  const onSearch = (value: string) => {
    console.log("onSearch", value);
    setWordSearch(value);
  };

  return (
    <div ref={setContainer}>
      <Affix offsetTop={0} target={() => container}>
        <div style={{ backgroundColor: "gray", height: "3.5em" }}>
          <Row style={{ paddingTop: "0.5em" }}>
            <Col xs={0} lg={8}></Col>
            <Col xs={24} lg={8}>
              <Search
                placeholder="input search text"
                onSearch={onSearch}
                style={{ width: "100%" }}
              />
            </Col>
            <Col xs={0} lg={8}></Col>
          </Row>
        </div>
      </Affix>
      <div>
        <Row>
          <Col flex="auto"></Col>
          <Col flex="1260px">
            <DictSearch word={wordSearch} />
          </Col>
          <Col flex="auto"></Col>
        </Row>
      </div>
    </div>
  );
};

export default Widget;
