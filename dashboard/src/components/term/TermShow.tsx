import { useEffect, useState } from "react";
import { Layout, Affix, Col, Row } from "antd";

import SearchVocabulary from "../dict/SearchVocabulary";
import TermSearch from "./TermSearch";

const { Content } = Layout;

interface IWidget {
  word?: string;
  wordId?: string;
  compact?: boolean;
  hideInput?: boolean;
  onSearch?: Function;
  onIdChange?: Function;
}
const TermShowWidget = ({
  word,
  wordId,
  compact = false,
  hideInput = false,
  onSearch,
  onIdChange,
}: IWidget) => {
  const [split, setSplit] = useState<string>();
  const [wordSearch, setWordSearch] = useState<string>();
  const [container, setContainer] = useState<HTMLDivElement | null>(null);

  useEffect(() => {
    setWordSearch(word?.toLowerCase());
  }, [word]);
  const dictSearch = (value: string, isFactor?: boolean) => {
    console.log("onSearch", value);
    const word = value.toLowerCase();
    setWordSearch(word);
    if (typeof onSearch !== "undefined") {
      onSearch(value, isFactor);
    }
  };
  return (
    <div ref={setContainer}>
      {hideInput ? (
        <></>
      ) : (
        <Affix offsetTop={0} target={compact ? () => container : undefined}>
          <div
            style={{
              backgroundColor: "rgba(100,100,100,0.3)",
              backdropFilter: "blur(5px)",
            }}
          >
            <Row style={{ paddingTop: "0.5em", paddingBottom: "0.5em" }}>
              {compact ? <></> : <Col flex="auto"></Col>}
              <Col flex="560px">
                <SearchVocabulary
                  value={word}
                  onSearch={dictSearch}
                  onSplit={(word: string | undefined) => {
                    console.log("onSplit", word);
                    setSplit(word);
                  }}
                />
              </Col>
              {compact ? <></> : <Col flex="auto"></Col>}
            </Row>
          </div>
        </Affix>
      )}

      <Content style={{ minHeight: 700 }}>
        <Row>
          {compact ? <></> : <Col flex="auto"></Col>}
          <Col flex="1260px">
            <TermSearch
              word={wordSearch}
              wordId={wordId}
              compact={compact}
              onIdChange={(value: string) => {
                console.debug("term onIdChange", value);
                if (typeof onIdChange !== "undefined") {
                  onIdChange(value);
                }
              }}
            />
          </Col>
          {compact ? <></> : <Col flex="auto"></Col>}
        </Row>
      </Content>
    </div>
  );
};

export default TermShowWidget;
