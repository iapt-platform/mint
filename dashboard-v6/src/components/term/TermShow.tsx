import { useState } from "react";
import { Layout, Affix, Col, Row } from "antd";

import TermSearch from "./TermSearch";
import SearchVocabulary from "../dict/SearchVocabulary";

const { Content } = Layout;

interface IWidget {
  word?: string;
  wordId?: string;
  compact?: boolean;
  hideInput?: boolean;
  onSearch?: (value: string, isFactor?: boolean) => void;
  onIdChange?: (value: string) => void;
}

const TermShowWidget = ({
  word,
  wordId,
  compact = false,
  hideInput = false,
  onSearch,
  onIdChange,
}: IWidget) => {
  const [localWord, setLocalWord] = useState<string | undefined>(
    word?.toLowerCase()
  );
  const [container, setContainer] = useState<HTMLDivElement | null>(null);

  const [prevWord, setPrevWord] = useState<string | undefined>(undefined);

  // 用 state 追踪 prop 变化（React 官方推荐的派生 state 写法）
  if (word !== prevWord) {
    setPrevWord(word);
    setLocalWord(word?.toLowerCase());
  }
  // 直接从 prop 派生，无需 useEffect
  const wordSearch = localWord;

  const dictSearch = (value: string, isFactor?: boolean) => {
    console.log("onSearch", value);
    setLocalWord(value.toLowerCase());
    onSearch?.(value, isFactor);
  };

  return (
    <div ref={setContainer}>
      {!hideInput && (
        <Affix offsetTop={0} target={compact ? () => container : undefined}>
          <div
            style={{
              backgroundColor: "rgba(100,100,100,0.3)",
              backdropFilter: "blur(5px)",
            }}
          >
            <Row style={{ paddingTop: "0.5em", paddingBottom: "0.5em" }}>
              {!compact && <Col flex="auto" />}
              <Col flex="560px">
                <SearchVocabulary
                  key={word}
                  value={word}
                  onSearch={dictSearch}
                />
              </Col>
              {!compact && <Col flex="auto" />}
            </Row>
          </div>
        </Affix>
      )}

      <Content style={{ minHeight: 700 }}>
        <Row>
          {!compact && <Col flex="auto" />}
          <Col flex="1260px">
            <TermSearch
              word={wordSearch}
              wordId={wordId}
              compact={compact}
              onIdChange={(value: string) => {
                console.debug("term onIdChange", value);
                onIdChange?.(value);
              }}
            />
          </Col>
          {!compact && <Col flex="auto" />}
        </Row>
      </Content>
    </div>
  );
};

export default TermShowWidget;
