import { useState } from "react";
import { Input, Space, Select } from "antd";

const { Search } = Input;

const FindWidget = () => {
  const [isLoading, setIsLoading] = useState(false);

  const onSearch = (value: string) => {
    setIsLoading(true);
    console.log(value);
  };
  const onReplace = (value: string) => {
    console.log(value);
  };
  return (
    <div>
      <Space direction="vertical">
        <Search
          placeholder="input search text"
          allowClear
          onSearch={onSearch}
          style={{ width: "100%" }}
          loading={isLoading}
        />
        <Search
          placeholder="input search text"
          allowClear
          enterButton="替换"
          style={{ width: "100%" }}
          onSearch={onReplace}
        />
        <Select
          defaultValue="current"
          style={{ width: "100%" }}
          onChange={(value: string) => {
            console.log(`selected ${value}`);
          }}
          options={[
            {
              value: "current",
              label: "当前文档",
            },
            {
              value: "all",
              label: "全部译文",
            },
          ]}
        />
        <div>搜索结果</div>
      </Space>
    </div>
  );
};

export default FindWidget;
