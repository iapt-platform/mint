import { get } from "../../request";
import { IVocabularyListResponse } from "../api/Dict";
import { useState } from "react";
import { AutoComplete, Input, Typography } from "antd";
import { DictIcon } from "../../assets/icon";

const { Text } = Typography;

interface ValueType {
  key?: string;
  label: React.ReactNode;
  value: string | number;
}
interface IWidget {
  onSearch?: Function;
}
const Widget = ({ onSearch }: IWidget) => {
  const [options, setOptions] = useState<ValueType[]>([]);
  const [fetching, setFetching] = useState(false);
  const [searchKey, setSearchKey] = useState<string>("");

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

  const search = (value: string) => {
    console.log("search", value);
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
        console.log("finally", searchKey);
        setFetching(false);
        if (searchKey !== "") {
          //setSearchKey("");
          //setFetching(true);
          //search(searchKey);
        }
      });
  };
  return (
    <div style={{ width: "100%" }}>
      <AutoComplete
        style={{ width: "100%" }}
        popupClassName="certain-category-search-dropdown"
        dropdownMatchSelectWidth={400}
        options={options}
        onSearch={(value: string) => {
          console.log("auto complete on search", value);
          if (fetching) {
            console.log("fetching");
            setSearchKey(value);
          } else {
            setFetching(true);
            setSearchKey("");
            search(value);
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
    </div>
  );
};

export default Widget;
