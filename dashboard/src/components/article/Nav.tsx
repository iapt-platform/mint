import { Space, Select } from "antd";

const NavWidget = () => {
  return (
    <div>
      <Space direction="vertical">
        <Select
          defaultValue="current"
          style={{ width: "100%" }}
          onChange={(value: string) => {
            console.log(`selected ${value}`);
          }}
          options={[
            {
              value: "book-mark",
              label: "书签",
            },
            {
              value: "tag",
              label: "标签",
            },
            {
              value: "suggestion",
              label: "修改建议",
            },
            {
              value: "qa",
              label: "问答",
            },
          ]}
        />
        <div>搜索结果</div>
      </Space>
    </div>
  );
};

export default NavWidget;
