import { get } from "../../request";
import type { IVocabularyListResponse } from "../../api/dict";
import { useEffect, useRef, useState } from "react";
import { AutoComplete, Input, Space, Typography } from "antd";
import { DictIcon } from "../../assets/icon";
import { useDebouncedCallback } from "./hooks/useDebouncedCallback";

const { Text, Link } = Typography;

interface ValueType {
  key?: string;
  label: React.ReactNode;
  value: string | number;
}
interface IWidget {
  value?: string;
  api?: string;
  compact?: boolean;
  onSearch?: (value: string, split?: boolean) => void;
  onSplit?: (value?: string) => void;
}

const SearchVocabulary = ({
  value,
  api = "vocabulary",
  compact = false,
  onSplit,
  onSearch,
}: IWidget) => {
  const [options, setOptions] = useState<ValueType[]>([]);
  const [fetching, setFetching] = useState(false);
  const [input, setInput] = useState<string | undefined>(value);
  const [factors, setFactors] = useState<string[]>([]);
  // 请求序号：用于竞态防护，只保留最新一次请求的结果
  const seqRef = useRef(0);
  // 回车只认键盘选中的候选项：鼠标悬停也会让某项高亮，此时回车 AutoComplete
  // 会选中悬停项（先触发 Input.Search 的 onSearch 查输入的词，紧接着 onSelect
  // 查悬停的词，后者覆盖前者）。用户要的是查自己输入的词。
  const arrowNavRef = useRef(false); // 本次输入后是否按过 ↑↓
  const enterRef = useRef(false); // 这次 onSelect 是否由回车触发
  const typedRef = useRef<string | undefined>(undefined); // 回车那一刻输入框里的词

  // 外部 value（点击单词触发的查词）变化时，同步输入框显示。
  // 仅在 value 真正变化时同步，用户手动输入时 value 不会变，因此不会打断输入。
  useEffect(() => {
    setInput(value);
  }, [value]);

  const renderItem = (title: string, count: number, meaning?: string) => ({
    value: title,
    label: (
      <div>
        <div style={{ display: "flex", justifyContent: "space-between" }}>
          {title}
          <span>
            <DictIcon /> {count}
          </span>
        </div>
        <div>
          <Text type="secondary">{meaning}</Text>
        </div>
      </div>
    ),
  });

  const factorChange = (word?: string) => {
    if (typeof word === "undefined" || word.includes(":")) {
      setFactors([]);
      return;
    }
    const strFactors = word.replaceAll("+", "-");
    if (strFactors.indexOf("-") >= 0) {
      setFactors(strFactors.split("-"));
      onSplit?.(strFactors.replaceAll("-", "+"));
    } else {
      setFactors([]);
      onSplit?.(undefined);
    }
  };

  const search = (value: string) => {
    if (value === "") return;

    // 记录本次请求序号，响应回来时若已不是最新序号则丢弃（竞态防护）
    const seq = ++seqRef.current;

    get<IVocabularyListResponse>(`/api/v2/${api}?view=key&key=${value}`)
      .then((json) => {
        if (seq !== seqRef.current) return;
        const words: ValueType[] = json.data.rows
          .map((item) => {
            let weight = item.count / (item.strlen - value.length + 0.1);
            if (item.word.length === value.length) {
              weight = 100;
            }
            return {
              word: item.word,
              count: item.count,
              meaning: item.meaning,
              weight,
            };
          })
          .sort((a, b) => b.weight - a.weight)
          .slice(0, 7)
          .map((item) => renderItem(item.word, item.count, item.meaning));

        setOptions(words);
      })
      .finally(() => {
        if (seq === seqRef.current) {
          setFetching(false);
        }
      });
  };

  // 输入补全防抖：连续输入 300ms 内只发最后一次请求
  const { debounced: debouncedSearch, cancel: cancelSearch } =
    useDebouncedCallback(search, 300);

  return (
    <div style={{ width: "100%" }}>
      {fetching ? <></> : null}
      <AutoComplete
        getPopupContainer={() =>
          document.getElementsByClassName("dict_search_div")[0] as HTMLElement
        }
        value={input}
        style={{ width: "100%" }}
        classNames={{ popup: { root: "certain-category-search-dropdown" } }}
        popupMatchSelectWidth={400}
        options={options}
        onChange={(val: string) => {
          setInput(val);
          factorChange(val);
        }}
        showSearch={{
          onSearch: (val: string) => {
            // 只有打字会走这里（选中候选项不会），借此重置键盘选择
            arrowNavRef.current = false;
            if (val === "") {
              // 空输入：立即取消挂起请求，并使在途请求失效，清空建议
              seqRef.current++;
              cancelSearch();
              setOptions([]);
              setFetching(false);
              return;
            }
            setFetching(true);
            debouncedSearch(val);
          },
        }}
        onSelect={(val: string) => {
          if (enterRef.current && !arrowNavRef.current) {
            // 回车但没用 ↑↓ 选过：高亮来自鼠标悬停，忽略。Input.Search 的
            // onSearch 已经查过输入的词，这里把被改掉的输入框改回来
            setInput(typedRef.current);
            return;
          }
          onSearch?.(val);
        }}
      >
        <Input.Search
          style={{ width: "100%" }}
          size={compact ? undefined : "large"}
          placeholder="search here"
          loading={fetching}
          onKeyDown={(e) => {
            if (e.key === "ArrowUp" || e.key === "ArrowDown") {
              arrowNavRef.current = true;
            } else if (e.key === "Enter") {
              // onSelect 与本次 keydown 同步触发；之后清掉，免得影响鼠标点击选择
              enterRef.current = true;
              setTimeout(() => {
                enterRef.current = false;
              }, 0);
            }
          }}
          onSearch={(val: string) => {
            // 回车时它先于 onSelect 触发，val 就是用户输入的词
            typedRef.current = val;
            onSearch?.(val);
          }}
        />
      </AutoComplete>
      <Space style={{ display: "none" }}>
        {factors.map((item, id) => (
          <Link
            key={id}
            onClick={() => {
              onSearch?.(item, true);
            }}
          >
            {item}
          </Link>
        ))}
      </Space>
    </div>
  );
};

export default SearchVocabulary;
