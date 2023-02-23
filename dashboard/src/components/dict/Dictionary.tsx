import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Layout, Affix, Col, Row } from "antd";

import DictSearch from "./DictSearch";
import SearchVocabulary from "./SearchVocabulary";

const { Content } = Layout;

interface IWidget {
  word?: string;
  compact?: boolean;
}
const Widget = ({ word, compact = false }: IWidget) => {
  const navigate = useNavigate();
  const [wordSearch, setWordSearch] = useState<string>();
  const [container, setContainer] = useState<HTMLDivElement | null>(null);

  useEffect(() => {
    setWordSearch(word?.toLowerCase());
  }, [word]);

  const onSearch = (value: string) => {
    console.log("onSearch", value);
    const word = value.toLowerCase();
    setWordSearch(word);
    if (compact === false) {
      navigate("/dict/" + word);
    }
  };
  return (
    <div ref={setContainer}>
      <Affix offsetTop={0} target={compact ? () => container : undefined}>
        <div style={{ backgroundColor: "gainsboro" }}>
          <Row style={{ paddingTop: "0.5em", paddingBottom: "0.5em" }}>
            {compact ? <></> : <Col flex="auto"></Col>}
            <Col flex="560px">
              <SearchVocabulary onSearch={onSearch} />
            </Col>
            {compact ? <></> : <Col flex="auto"></Col>}
          </Row>
        </div>
      </Affix>
      <Content style={{ minHeight: 700 }}>
        <Row>
          {compact ? <></> : <Col flex="auto"></Col>}
          <Col flex="1260px">
            <DictSearch word={wordSearch} compact={compact} />
          </Col>
          {compact ? <></> : <Col flex="auto"></Col>}
        </Row>
      </Content>
    </div>
  );
};

export default Widget;
