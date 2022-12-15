import { useState, useEffect } from "react";
import { Affix, Col, Row } from "antd";
import { Input } from "antd";

import { useAppSelector } from "../../hooks";
import { message } from "../../reducers/command";

import DictSearch from "./DictSearch";

const { Search } = Input;

export interface IWidgetDict {
  word?: string;
}
const Widget = ({ word }: IWidgetDict) => {
  const [container, setContainer] = useState<HTMLDivElement | null>(null);
  const [wordSearch, setWordSearch] = useState(word);

  const onSearch = (value: string) => {
    console.log("onSearch", value);
    setWordSearch(value);
  };

  //接收查字典消息
  const commandMsg = useAppSelector(message);
  useEffect(() => {
    console.log("get command", commandMsg);
    if (commandMsg?.type === "dict") {
      setWordSearch(commandMsg.prop?.word);
    }
  }, [commandMsg]);

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
                value={wordSearch}
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
