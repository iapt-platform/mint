import { title1 } from "@uiw/react-md-editor";
import { Button, Tree } from "antd";
import { Key } from "antd/lib/table/interface";
import { useState } from "react";

interface DataNode {
  title: string;
  key: string;
  children?: DataNode[];
}

const Widget = () => {
  const [selectedKeys, setSelectedKeys] = useState<Key[]>();
  const [expandedKeys, setExpandedKeys] = useState<Key[]>();
  const [treeData, setTreeData] = useState<DataNode[]>([
    {
      title: "title1",
      key: "title1",
      children: [
        { title: "title1-1", key: "title1-1" },
        { title: "title1-2", key: "title1-2" },
      ],
    },
    { title: "title2", key: "title2" },
  ]);
  return (
    <>
      <Button
        onClick={() => {
          setTreeData((origin) => {
            return [...origin, { title: "title3", key: "title3" }];
          });
        }}
      >
        add
      </Button>
      <Button
        onClick={() => {
          setExpandedKeys(["title1"]);
          setSelectedKeys(["title1-2"]);
        }}
      >
        expand
      </Button>
      <Tree
        treeData={treeData}
        expandedKeys={expandedKeys}
        selectedKeys={selectedKeys}
        onExpand={(expandedKeys: Key[]) => {
          console.log("expandedKeys", expandedKeys);
          setSelectedKeys(expandedKeys);
        }}
        onSelect={(selectedKeys: Key[]) => setSelectedKeys(selectedKeys)}
      />
    </>
  );
};

export default Widget;
