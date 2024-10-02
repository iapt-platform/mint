import type { TreeProps } from "antd/es/tree";
import TocTree from "../article/TocTree";
import { ListNodeData } from "../article/EditableTree";

const treeData: ListNodeData[] = [
  {
    title: "title 1",
    key: "0-1",
    level: 1,
  },
  {
    title: "title 2",
    key: "0-2",
    level: 2,
  },
  {
    title: "title 1",
    key: "0-3",
    level: 2,
  },
  {
    title: "title 1",
    key: "1-0",
    level: 1,
  },
];

const Widget = () => {
  const onSelect: TreeProps["onSelect"] = (selectedKeys, info) => {
    console.log("selected", selectedKeys, info);
  };

  return (
    <TocTree onSelect={onSelect} treeData={treeData} expandedKeys={["0-3"]} />
  );
};

export default Widget;
