import { get } from "../../request";
import { IVocabularyListResponse } from "../api/Dict";
import { useEffect, useRef, useState } from "react";
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
  onSearch?: Function;
  onSplit?: Function;
}
const Widget = ({ value, onSplit, onSearch }: IWidget) => {
  const [options, setOptions] = useState<ValueType[]>([]);
  const [fetching, setFetching] = useState(false);

  const [input, setInput] = useState<string>();
  const [factors, setFactors] = useState<string[]>([]);
  const intervalRef = useRef<number | null>(null); //防抖计时器句柄

  useEffect(() => {
    setInput(value);
    console.log("dict input", value);
  }, [value]);

  const renderItem = (title: string, count: number, meaning?: string) => ({
    value: title,
    label: (
      <div>
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
          }}
        >
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

  /**
   * 停止查字典计时
   * 在两种情况下停止计时
   * 1. 开始查字典
   * 2. 防抖时间内鼠标移出单词区
   */
  const stopLookup = () => {
    if (intervalRef.current) {
      window.clearInterval(intervalRef.current);
      intervalRef.current = null;
    }
  };

  const search = (value: string) => {
    console.log("search", value);
    stopLookup();
    if (value === "") {
      return;
    }

    get<IVocabularyListResponse>(`/v2/vocabulary?view=key&key=${value}`)
      .then((json) => {
        const words: ValueType[] = json.data.rows.map((item) => {
          return renderItem(item.word, item.count, item.meaning);
        });
        setOptions(words);
      })
      .finally(() => {
        console.log("finally");
        setFetching(false);
      });
  };

  const startLookup = (value: string) => {
    //开始计时，计时结束查字典
    console.log("开始计时");
    intervalRef.current = window.setInterval(search, 500, value);
  };
  return (
    <div style={{ width: "100%" }}>
      <AutoComplete
        value={input}
        style={{ width: "100%" }}
        popupClassName="certain-category-search-dropdown"
        dropdownMatchSelectWidth={400}
        options={options}
        onChange={(value: string, option: ValueType | ValueType[]) => {
          console.log("input", value);
          setInput(value);

          const strFactors = value.replaceAll("+", "-");
          if (strFactors.indexOf("-") >= 0) {
            setFactors(strFactors.split("-"));
            if (typeof onSplit !== "undefined") {
              onSplit(strFactors.replaceAll("-", "+"));
            }
          } else {
            setFactors([]);
            if (typeof onSplit !== "undefined") {
              onSplit();
            }
          }
        }}
        onSearch={(value: string) => {
          console.log("auto complete on search", value);
          if (fetching) {
            console.log("fetching");
          } else {
            setFetching(true);
            search(value);
            //stopLookup();
            //startLookup(value);
          }
        }}
        onSelect={(value: string, option: ValueType) => {
          if (typeof onSearch !== "undefined") {
            onSearch(value);
          }
        }}
      >
        <Input.Search
          size="large"
          placeholder="input here"
          onSearch={(value: string) => {
            console.log("on search", value);
            if (typeof onSearch !== "undefined") {
              onSearch(value);
            }
          }}
        />
      </AutoComplete>
      <Space>
        {factors.map((item, id) => {
          return (
            <Link
              key={id}
              onClick={() => {
                if (typeof onSearch !== "undefined") {
                  onSearch(item, true);
                }
              }}
            >
              {item}
            </Link>
          );
        })}
      </Space>
    </div>
  );
};

export default Widget;
