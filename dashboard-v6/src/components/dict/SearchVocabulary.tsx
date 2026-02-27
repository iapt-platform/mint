import { get } from "../../request";
import type { IVocabularyListResponse } from "../../api/Dict";
import { useRef, useState } from "react";
import { AutoComplete, Input, Space, Typography } from "antd";
import { DictIcon } from "../../assets/icon";

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
  onSplit?: (value: string | null) => void;
}

const SearchVocabularyWidget = ({
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
  const intervalRef = useRef<number | null>(null);

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

  const stopLookup = () => {
    if (intervalRef.current) {
      window.clearInterval(intervalRef.current);
      intervalRef.current = null;
    }
  };

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
      onSplit?.(null);
    }
  };

  const search = (value: string) => {
    stopLookup();
    if (value === "") return;

    get<IVocabularyListResponse>(`/v2/${api}?view=key&key=${value}`)
      .then((json) => {
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
        setFetching(false);
      });
  };

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
            setFetching(true);
            search(val);
          },
        }}
        onSelect={(val: string) => {
          onSearch?.(val);
        }}
      >
        <Input.Search
          style={{ width: "100%" }}
          size={compact ? undefined : "large"}
          placeholder="search here"
          onSearch={(val: string) => {
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

export default SearchVocabularyWidget;
