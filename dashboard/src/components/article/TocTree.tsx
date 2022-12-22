import { Tree } from "antd";

import type { DataNode, TreeProps } from "antd/es/tree";
import { useEffect, useState } from "react";
import type { ListNodeData } from "../studio/EditableTree";
import PaliText from "../template/Wbw/PaliText";

type TreeNodeData = {
  key: string;
  title: string;
  children: TreeNodeData[];
  level: number;
};

function tocGetTreeData(
  listData: ListNodeData[],
  active = ""
): [TreeNodeData[], string] {
  let treeData: TreeNodeData[] = [];
  let tocActivePath: TreeNodeData[] = [];
  let treeParents = [];
  let defaultExpandedKey: string = "";
  let rootNode: TreeNodeData = {
    key: "0",
    title: "root",
    level: 0,
    children: [],
  };
  treeData.push(rootNode);
  let lastInsNode: TreeNodeData = rootNode;

  let iCurrLevel = 0;
  for (let index = 0; index < listData.length; index++) {
    const element = listData[index];

    let newNode: TreeNodeData = {
      key: element.key,
      title: element.title,
      children: [],
      level: element.level,
    };

    if (active === element.key) {
      defaultExpandedKey = element.key;
    }

    if (newNode.level > iCurrLevel) {
      //新的层级比较大，为上一个的子目录
      treeParents.push(lastInsNode);
      lastInsNode.children.push(newNode);
    } else if (newNode.level === iCurrLevel) {
      //目录层级相同，为平级
      treeParents[treeParents.length - 1].children.push(newNode);
    } else {
      // 小于 挂在上一个层级
      while (treeParents.length > 1) {
        treeParents.pop();
        if (treeParents[treeParents.length - 1].level < newNode.level) {
          break;
        }
      }
      treeParents[treeParents.length - 1].children.push(newNode);
    }
    lastInsNode = newNode;
    iCurrLevel = newNode.level;

    if (active === element.key) {
      tocActivePath = [];
      for (let index = 1; index < treeParents.length; index++) {
        //treeParents[index]["expanded"] = true;
        tocActivePath.push(treeParents[index]);
      }
    }
  }
  return [treeData[0].children, defaultExpandedKey];
}

interface IWidgetTocTree {
  treeData: ListNodeData[];
  expandedKey?: string;
}

const Widget = ({ treeData, expandedKey }: IWidgetTocTree) => {
  const [tree, setTree] = useState<TreeNodeData[]>();
  const [expanded, setExpanded] = useState<string>("");

  useEffect(() => {
    if (treeData.length > 0) {
      const [data, key] = tocGetTreeData(treeData, expandedKey);
      setTree(data);
      setExpanded(key);
      console.log("create tree", treeData.length, expandedKey, key);
    }
  }, [treeData, expandedKey]);
  const onSelect: TreeProps["onSelect"] = (selectedKeys, info) => {
    //let aaa: NewTree = info.node;
    console.log("selected", selectedKeys);
  };

  return (
    <Tree
      onSelect={onSelect}
      treeData={tree}
      defaultExpandedKeys={[expanded]}
      defaultSelectedKeys={[expanded]}
      blockNode
      titleRender={(node: DataNode) => {
        if (typeof node.title === "string") {
          return <PaliText text={node.title} />;
        } else {
          return <></>;
        }
      }}
    />
  );
};

export default Widget;
