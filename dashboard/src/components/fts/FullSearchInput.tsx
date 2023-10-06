import { AutoComplete, Badge, Input, Select, Space, Typography } from "antd";
import { SizeType } from "antd/lib/config-provider/SizeContext";
import { useState } from "react";
import { get } from "../../request";
import { ISearchView } from "./FullTextSearchResult";

const { Text } = Typography;
const { Option } = Select;

export interface IWordIndexData {
  word: string;
  count: number;
  bold: number;
}
export interface IWordIndexListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IWordIndexData[];
    count: number;
  };
}

interface ValueType {
  key?: string;
  label: React.ReactNode;
  value: string | number;
}
interface IWidget {
  value?: string;
  tags?: string[];
  book?: number;
  para?: number;
  size?: SizeType;
  width?: string | number;
  view?: ISearchView;
  onSearch?: Function;
  onSplit?: Function;
  onPageTypeChange?: Function;
}
const FullSearchInputWidget = ({
  value,
  onSplit,
  tags,
  size = "middle",
  width,
  onSearch,
  view = "pali",
  onPageTypeChange,
}: IWidget) => {
  const [options, setOptions] = useState<ValueType[]>([]);
  const [input, setInput] = useState<string | undefined>(value);

  const renderItem = (word: string, count: number, bold: number) => ({
    value: word,
    label: (
      <div>
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
          }}
        >
          <span>{bold > 0 ? <Text strong>{word}</Text> : word}</span>
          <Badge color="geekblue" count={count} />
        </div>
      </div>
    ),
  });
  const search = (value: string) => {
    console.log("search", value);
    if (value === "") {
      return;
    }
    let url: string = "";
    switch (view) {
      case "pali":
        url = `/v2/pali-word-index?view=key&key=${value}`;
        break;
      case "title":
        url = `/v2/search-title-index?&key=${value}`;
        break;
      default:
        break;
    }
    get<IWordIndexListResponse>(url).then((json) => {
      const words: ValueType[] = json.data.rows.map((item) => {
        return renderItem(item.word, item.count, item.bold);
      });
      setOptions(words);
    });
  };

  const selectBefore = (
    <Select
      defaultValue="P"
      size="large"
      style={{ width: 120 }}
      onChange={(value: string) => {
        if (typeof onPageTypeChange !== "undefined") {
          onPageTypeChange(value);
        }
      }}
    >
      <Option value="P">PTS</Option>
      <Option value="M">Myanmar</Option>
      <Option value="T">Thai</Option>
      <Option value="V">VRI</Option>
      <Option value="O">Other</Option>
    </Select>
  );
  return (
    <Space>
      {view === "page" ? selectBefore : undefined}
      <AutoComplete
        style={{ width: width }}
        value={input}
        popupClassName="certain-category-search-dropdown"
        dropdownMatchSelectWidth={400}
        options={options}
        onChange={(value: string, option: ValueType | ValueType[]) => {
          console.log("input", value);
          setInput(value);
        }}
        onSearch={(value: string) => {
          console.log("auto complete on search", value, tags);
          if (value.indexOf(" ") >= 0 || value.indexOf(";") >= 0) {
            const valueLast = value.split(/[ ]|;/).slice(-1);
            search(valueLast[0]);
          } else {
            search(value);
          }
        }}
        onSelect={(value: string, option: ValueType) => {
          if (typeof onSearch !== "undefined") {
            if (
              typeof input === "string" &&
              (input.indexOf(" ") >= 0 || input.indexOf(";") >= 0)
            ) {
              const last1 = input.lastIndexOf(" ");
              const last2 = input.lastIndexOf(";");
              let searchString = "";
              if (last1 > last2) {
                searchString = input.slice(0, last1 + 1) + value;
              } else {
                searchString = input.slice(0, last2 + 1) + value;
              }
              onSearch(searchString);
              setInput(searchString);
            } else {
              onSearch(value);
            }
          }
        }}
      >
        <Input.Search
          size={size}
          width={width}
          placeholder={
            view === "page"
              ? "输入页码数字，或者卷号.页码如 1.1"
              : "search here"
          }
          onSearch={(value: string) => {
            console.log("on search", value, tags);
            if (typeof onSearch !== "undefined") {
              onSearch(value);
            }
          }}
        />
      </AutoComplete>
    </Space>
  );
};

export default FullSearchInputWidget;
