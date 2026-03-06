import { Badge, Button, Card, Checkbox, Select, Space, Typography } from "antd";
import { DownOutlined, UpOutlined } from "@ant-design/icons";
import { useEffect, useMemo, useReducer, useRef, useState } from "react";

import { get } from "../../request";
import type {
  ICaseItem,
  ICaseListData,
  ICaseListResponse,
} from "../../api/dict";
import type { CheckboxChangeEvent } from "antd/es/checkbox";

const { Text } = Typography;

interface IWidget {
  word?: string;
  lines?: number;
  onChange?: (checkedList: string[]) => void;
}
// TODO 移除复杂的状态管理
// ---------------------------------------------------------------------------
// State shape + reducer — all mutations go through dispatch, never setState
// ---------------------------------------------------------------------------
interface State {
  words: ICaseItem[];
  currWord: string | undefined;
  checkedList: string[];
}

type Action =
  | {
      type: "FETCHED";
      words: ICaseItem[];
      currWord: string;
      checkedList: string[];
    }
  | { type: "SELECT_WORD"; currWord: string; checkedList: string[] }
  | { type: "SET_CHECKED"; checkedList: string[] };

const INITIAL_STATE: State = {
  words: [],
  currWord: undefined,
  checkedList: [],
};

function reducer(state: State, action: Action): State {
  switch (action.type) {
    case "FETCHED":
      return {
        words: action.words,
        currWord: action.currWord,
        checkedList: action.checkedList,
      };
    case "SELECT_WORD":
      return {
        ...state,
        currWord: action.currWord,
        checkedList: action.checkedList,
      };
    case "SET_CHECKED":
      return { ...state, checkedList: action.checkedList };
    default:
      return state;
  }
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------
function sortedCases(cases: ICaseListData[]): ICaseListData[] {
  return cases.slice().sort((a, b) => b.count - a.count);
}

function allWords(cases: ICaseListData[]): string[] {
  return sortedCases(cases).map((c) => c.word);
}

// ---------------------------------------------------------------------------
// Component
// ---------------------------------------------------------------------------
const CaseListWidget = ({ word, lines, onChange }: IWidget) => {
  const [showAll, setShowAll] = useState(!lines);
  const [state, dispatch] = useReducer(reducer, INITIAL_STATE);

  // Keep a stable ref to onChange — avoids adding it to effect deps
  const onChangeRef = useRef(onChange);
  useEffect(() => {
    onChangeRef.current = onChange;
  });

  // -------------------------------------------------------------------------
  // Determine whether the word prop is usable (no spaces)
  // This is pure computation — no effect needed.
  // When word has spaces we simply treat state as empty during render.
  // -------------------------------------------------------------------------
  const isMultiWord = typeof word === "string" && word.trim().includes(" ");

  const { words, currWord, checkedList } = isMultiWord ? INITIAL_STATE : state;

  // Derive caseData in render — no state, no effect
  const caseData: ICaseListData[] | undefined = useMemo(
    () =>
      currWord
        ? words
            .find((item) => item.word === currWord)
            ?.case.slice()
            .sort((a, b) => b.count - a.count)
        : undefined,
    [words, currWord]
  );

  // -------------------------------------------------------------------------
  // Fetch effect — dispatch only happens inside the async .then() callback,
  // which is NOT synchronous within the effect body → lint rule satisfied.
  // -------------------------------------------------------------------------
  useEffect(() => {
    if (typeof word === "undefined" || word.trim().includes(" ")) return;

    let cancelled = false;

    get<ICaseListResponse>(`/api/v2/case/${word}`).then((json) => {
      if (cancelled) return;
      if (json.ok && json.data.rows.length > 0) {
        const sorted = json.data.rows.slice().sort((a, b) => b.count - a.count);
        dispatch({
          type: "FETCHED",
          words: sorted,
          currWord: sorted[0].word,
          checkedList: allWords(sorted[0].case),
        });
      }
    });

    return () => {
      cancelled = true;
    };
  }, [word]);

  // -------------------------------------------------------------------------
  // Notify parent — only dispatches to onChangeRef (external system), no setState
  // -------------------------------------------------------------------------
  const prevCheckedRef = useRef<string[]>([]);
  useEffect(() => {
    if (checkedList.length > 0 && checkedList !== prevCheckedRef.current) {
      onChangeRef.current?.(checkedList);
    }
    prevCheckedRef.current = checkedList;
  }, [checkedList]);

  // -------------------------------------------------------------------------
  // Event handlers — all state changes happen here, never inside effects
  // -------------------------------------------------------------------------
  const handleWordChange = (value: string) => {
    const cases = words.find((item) => item.word === value)?.case ?? [];
    dispatch({
      type: "SELECT_WORD",
      currWord: value,
      checkedList: allWords(cases),
    });
  };

  const handleCheckAll = (e: CheckboxChangeEvent) => {
    dispatch({
      type: "SET_CHECKED",
      checkedList: e.target.checked
        ? (caseData?.map((item) => item.word) ?? [])
        : [],
    });
  };

  const handleCheckedChange = (list: string[]) => {
    dispatch({ type: "SET_CHECKED", checkedList: list as string[] });
  };

  // -------------------------------------------------------------------------
  // Derived display values
  // -------------------------------------------------------------------------
  const checkAll = !!caseData && caseData.length === checkedList.length;
  const indeterminate =
    !!caseData &&
    checkedList.length > 0 &&
    checkedList.length < caseData.length;
  const showWords = showAll ? caseData : caseData?.slice(0, lines);

  return (
    <div style={{ padding: 4 }}>
      {currWord ? (
        <Card
          size="small"
          extra={
            lines ? (
              <Button type="link" onClick={() => setShowAll((prev) => !prev)}>
                <Space>
                  {showAll ? "折叠" : "展开"}
                  {showAll ? <UpOutlined /> : <DownOutlined />}
                </Space>
              </Button>
            ) : null
          }
          title={
            <Select
              value={currWord}
              variant="borderless"
              onChange={handleWordChange}
              options={words.map((item) => ({
                label: (
                  <Space>
                    {item.word}
                    <Badge
                      count={item.count}
                      color="lime"
                      status="default"
                      size="small"
                    />
                  </Space>
                ),
                value: item.word,
              }))}
            />
          }
        >
          <Checkbox
            indeterminate={indeterminate}
            onChange={handleCheckAll}
            checked={checkAll}
          >
            Check all
          </Checkbox>
          <Checkbox.Group
            style={{ display: "grid" }}
            options={showWords?.map((item) => ({
              label: (
                <Space>
                  <Text strong={item.bold > 0}>{item.word}</Text>
                  <Badge
                    size="small"
                    count={item.count}
                    overflowCount={9999}
                    status="default"
                  />
                </Space>
              ),
              value: item.word,
            }))}
            value={checkedList}
            onChange={(list) => handleCheckedChange(list as string[])}
          />
        </Card>
      ) : (
        <Text>多词搜索没有变格词表</Text>
      )}
    </div>
  );
};

export default CaseListWidget;
