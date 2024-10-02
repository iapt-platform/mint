import { useEffect, useState } from "react";
import { Layout, Affix, Col, Row } from "antd";

import DictSearch from "./DictSearch";
import SearchVocabulary from "./SearchVocabulary";
import Compound from "./Compound";
import TermShow from "../term/TermShow";

const { Content } = Layout;

interface IWidget {
  word?: string;
  compact?: boolean;
  onSearch?: Function;
}
const DictionaryWidget = ({ word, compact = false, onSearch }: IWidget) => {
  const [split, setSplit] = useState<string>();
  const [wordSearch, setWordSearch] = useState<string>();
  const [container, setContainer] = useState<HTMLDivElement | null>(null);
  const [dictType, setDictType] = useState("dict");
  const [wordInput, setWordInput] = useState(word);
  const [wordId, setWordId] = useState<string>();

  useEffect(() => {
    console.debug("param word change", word);
    wordInputChange(word);
  }, [word]);

  const wordChange = (value?: string) => {
    console.debug("has ':'", value, value?.includes(":"));
    let currWord: string | undefined = "";
    if (value?.includes(":")) {
      const param = value.split(" ");
      param.forEach((value) => {
        const kv = value.split(":");
        if (kv.length === 2) {
          switch (kv[0]) {
            case "type":
              setDictType(kv[1]);
              break;
            case "word":
              currWord = kv[1];
              break;
            case "id":
              setWordId(kv[1]);
              break;
            default:
              break;
          }
        }
      });
    } else {
      setDictType("dict");
      currWord = value?.toLowerCase();
    }
    document.getElementById("pcd_dict_top")?.scrollIntoView();
    return currWord;
  };

  const wordInputChange = (value: string | undefined) => {
    setWordInput(value);
    console.debug("wordInput change", value);
    if (value !== wordSearch) {
      const currWord = wordChange(value);
      setWordSearch(currWord);
    }
  };

  const dictSearch = (value: string, isFactor?: boolean) => {
    console.info("onSearch", value);
    const currWord = wordChange(value);
    if (typeof onSearch !== "undefined" && !isFactor) {
      onSearch(currWord);
    }
    setWordSearch(currWord);
  };
  return (
    <div ref={setContainer}>
      <div id="pcd_dict_top"></div>
      <Affix
        offsetTop={0}
        target={compact ? () => container : undefined}
        className="dict_search_div"
      >
        <div
          style={{
            backgroundColor: "rgba(100,100,100,0.3)",
            backdropFilter: "blur(5px)",
          }}
        >
          <Row style={{ paddingTop: "0.5em", paddingBottom: "0.5em" }}>
            {compact ? <></> : <Col flex="auto"></Col>}
            <Col flex="560px">
              <div style={{ display: "flex" }}>
                <SearchVocabulary
                  compact={compact}
                  value={wordInput?.toLowerCase()}
                  onSearch={dictSearch}
                  onSplit={(word: string | undefined) => {
                    console.log("onSplit", word);
                    setSplit(word);
                  }}
                />
              </div>
            </Col>
            {compact ? <></> : <Col flex="auto"></Col>}
          </Row>
        </div>
      </Affix>
      <Content style={{ minHeight: 700 }}>
        <Row>
          {compact ? <></> : <Col flex="auto"></Col>}
          <Col flex="1260px">
            {dictType === "dict" ? (
              <div>
                <Compound word={word} add={split} onSearch={dictSearch} />
                <DictSearch word={wordSearch} compact={compact} />
              </div>
            ) : (
              <TermShow
                word={wordSearch}
                wordId={wordId}
                hideInput
                onIdChange={(value: string) => {
                  const newInput = `type:term id:${value}`;
                  console.debug("term onIdChange setWordInput", newInput);
                  if (typeof onSearch !== "undefined") {
                    onSearch(newInput);
                  } else {
                    wordInputChange(newInput);
                  }
                }}
              />
            )}
          </Col>
          {compact ? <></> : <Col flex="auto"></Col>}
        </Row>
      </Content>
    </div>
  );
};

export default DictionaryWidget;
