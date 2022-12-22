import React from "react";
import { Tree } from "antd";
import type { DataNode, TreeProps } from "antd/es/tree";

const treeData: DataNode[] = [
  {
    title: "parent 1",
    key: "0-0",
    children: [
      {
        title: "parent 1-0",
        key: "0-0-0",
        children: [
          {
            title: "leaf",
            key: "0-0-0-0",
          },
          {
            title: "leaf",
            key: "0-0-0-1",
          },
        ],
      },
      {
        title: "parent 1-1",
        key: "0-0-1",
        children: [
          {
            title: <span style={{ color: "#1890ff" }}>sss</span>,
            key: "0-0-1-0",
          },
        ],
      },
    ],
  },
];

const Widget = () => {
  const onSelect: TreeProps["onSelect"] = (selectedKeys, info) => {
    console.log("selected", selectedKeys, info);
  };

  return (
    <Tree
      defaultExpandedKeys={["0-0-1-0"]}
      onSelect={onSelect}
      treeData={treeData}
    />
  );
};

export default Widget;
