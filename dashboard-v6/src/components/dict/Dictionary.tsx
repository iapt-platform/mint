import { useCallback, useEffect, useRef, useState } from "react";
import { Layout, Affix, Col, Row } from "antd";

import DictSearch from "./DictSearch";
import SearchVocabulary from "./SearchVocabulary";
import Compound from "./Compound";
import TermShow from "../term/TermShow";

const { Content } = Layout;

interface IWidget {
  word?: string;
  compact?: boolean;
  onSearch?: (word?: string) => void;
}

interface IQuery {
  type: string;
  word?: string;
  id?: string;
}

/**
 * 解析查词串。普通词按小写查字典；带冒号的是高级格式，
 * 如 `type:term id:xxx`、`type:term word:dhamma`。纯函数，不碰 state。
 */
function parseQuery(value?: string): IQuery {
  if (!value?.includes(":")) {
    return { type: "dict", word: value?.toLowerCase() };
  }
  // 与 v4 一致：没写 type 时沿用字典
  const query: IQuery = { type: "dict", word: "" };
  value.split(" ").forEach((item) => {
    const kv = item.split(":");
    if (kv.length !== 2) return;
    switch (kv[0]) {
      case "type":
        query.type = kv[1];
        break;
      case "word":
        query.word = kv[1];
        break;
      case "id":
        query.id = kv[1];
        break;
      default:
        break;
    }
  });
  return query;
}

const DictionaryWidget = ({ word, compact = false, onSearch }: IWidget) => {
  const [split, setSplit] = useState<string>();
  const [container, setContainer] = useState<HTMLDivElement | null>(null);
  // 右边栏可同时开多个字典 tab，不能用全局 id 定位顶部
  const topRef = useRef<HTMLDivElement>(null);
  // 首次挂载就带着 word 时（如从 URL 进入）直接按它查，不必等 prop 变化
  const [initial] = useState(() => parseQuery(word));
  const [wordSearch, setWordSearch] = useState(initial.word);
  const [dictType, setDictType] = useState(initial.type);
  const [wordId, setWordId] = useState(initial.id);
  const [wordInput, setWordInput] = useState(word);

  // 用 state 记录上一次的 prop，渲染期派生的标准做法
  const [prevWordProp, setPrevWordProp] = useState<string | undefined>(word);
  const [prevSearch, setPrevSearch] = useState<string | undefined>(word);

  /** 只更新本组件的 state，可在渲染期调用 */
  const applyQuery = (query: IQuery) => {
    setDictType(query.type);
    // 只有高级格式带 id；普通词不清掉旧 id，与 v4 一致
    if (query.id !== undefined) setWordId(query.id);
    setWordSearch(query.word);
  };

  // 渲染期派生：word prop 变化（路由前进后退、redux 查词）时同步输入框并查词
  if (word !== prevWordProp) {
    setPrevWordProp(word);
    setWordInput(word);
    if (word !== prevSearch) {
      setPrevSearch(word);
      applyQuery(parseQuery(word));
    }
  }

  // 换词后滚回顶部。副作用不能放在渲染期，所以放这里；首次挂载不滚
  const mounted = useRef(false);
  useEffect(() => {
    if (!mounted.current) {
      mounted.current = true;
      return;
    }
    topRef.current?.scrollIntoView();
  }, [wordSearch, dictType, wordId]);

  const wordInputChange = (value: string | undefined) => {
    setWordInput(value);
    if (value === prevSearch) {
      console.debug("same word, skip search", value);
      return;
    }
    setPrevSearch(value);
    applyQuery(parseQuery(value));
  };

  const dictSearch = useCallback(
    (value: string, isFactor?: boolean) => {
      console.info("onSearch", value);
      const query = parseQuery(value);
      // 与 v4 一致，交给外部（路由 / redux）的只是解析出的单词，搜索框里的
      // 高级格式不进地址栏；要重入请直接在地址栏写 type:term id:xxx
      if (typeof onSearch !== "undefined" && !isFactor) {
        onSearch(query.word);
      }
      // 用户主动搜索不去重。记下交给外部的词，它回传成 word prop 时不再重查，
      // 否则会按普通词重新解析，把刚切到的术语视图冲掉
      setPrevSearch(query.word);
      setDictType(query.type);
      if (query.id !== undefined) setWordId(query.id);
      setWordSearch(query.word);
    },
    [onSearch],
  );

  return (
    <div ref={setContainer}>
      <div ref={topRef}></div>
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
                  onSplit={(word) => {
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
