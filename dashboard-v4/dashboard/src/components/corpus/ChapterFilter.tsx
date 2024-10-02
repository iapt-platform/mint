import { Space, Input } from "antd";

import ChapterFilterType from "./ChapterFilterType";
import ChapterFilterLang from "./ChapterFilterLang";
import ChapterFilterProgress from "./ChapterFilterProgress";

const { Search } = Input;

interface IWidget {
  onTypeChange?: Function;
  onLangChange?: Function;
  onProgressChange?: Function;
  onSearch?: Function;
}
const ChapterFilterWidget = ({
  onTypeChange,
  onLangChange,
  onProgressChange,
  onSearch,
}: IWidget) => {
  return (
    <Space style={{ margin: 8 }}>
      <Search
        placeholder="标题搜索"
        onSearch={(value: string) => {
          if (typeof onSearch !== "undefined") {
            onSearch(value);
          }
        }}
        style={{ width: 200 }}
      />
      <ChapterFilterProgress
        onSelect={(value: string) => {
          if (typeof onProgressChange !== "undefined") {
            onProgressChange(value);
          }
        }}
      />

      <ChapterFilterType
        onSelect={(value: string) => {
          if (typeof onTypeChange !== "undefined") {
            onTypeChange(value);
          }
        }}
      />

      <ChapterFilterLang
        onSelect={(value: string) => {
          if (typeof onLangChange !== "undefined") {
            onLangChange(value);
          }
        }}
      />
    </Space>
  );
};

export default ChapterFilterWidget;
