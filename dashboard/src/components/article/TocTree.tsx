import { Tree } from "antd";

import type { DataNode, TreeProps } from "antd/es/tree";
import { useEffect, useState } from "react";
import type { ListNodeData } from "./EditableTree";
import PaliText from "../template/Wbw/PaliText";

type TreeNodeData = {
  key: string;
  title: string;
  children?: TreeNodeData[];
  level: number;
};

function tocGetTreeData(
  listData: ListNodeData[],
  active = ""
): TreeNodeData[] | undefined {
  let treeData: TreeNodeData[] = [];
  let tocActivePath: TreeNodeData[] = [];
  let treeParents = [];
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
      level: element.level,
    };

    if (newNode.level > iCurrLevel) {
      //新的层级比较大，为上一个的子目录
      treeParents.push(lastInsNode);
      if (typeof lastInsNode.children === "undefined") {
        lastInsNode.children = [];
      }
      lastInsNode.children.push(newNode);
    } else if (newNode.level === iCurrLevel) {
      //目录层级相同，为平级
      const parentNode = treeParents[treeParents.length - 1];
      if (typeof parentNode !== "undefined") {
        if (typeof parentNode.children === "undefined") {
          parentNode.children = [];
        }
        parentNode.children.push(newNode);
      }
    } else {
      // 小于 挂在上一个层级
      while (treeParents.length > 1) {
        treeParents.pop();
        if (treeParents[treeParents.length - 1].level < newNode.level) {
          break;
        }
      }
      const parentNode = treeParents[treeParents.length - 1];
      if (typeof parentNode !== "undefined") {
        if (typeof parentNode.children === "undefined") {
          parentNode.children = [];
        }
        parentNode.children.push(newNode);
      }
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

  return treeData[0].children;
}

interface IWidgetTocTree {
  treeData?: ListNodeData[];
  expandedKey?: string[];
  onSelect?: Function;
}

const Widget = ({ treeData, expandedKey, onSelect }: IWidgetTocTree) => {
  const [tree, setTree] = useState<TreeNodeData[]>();
  const [expanded, setExpanded] = useState(expandedKey);

  useEffect(() => {
    if (treeData && treeData.length > 0) {
      const data = tocGetTreeData(treeData);
      setTree(data);
      setExpanded(expandedKey);
      console.log("create tree", treeData.length, expandedKey);
    }
  }, [treeData, expandedKey]);
  const onNodeSelect: TreeProps["onSelect"] = (selectedKeys, info) => {
    console.log("selected", selectedKeys);
    if (typeof onSelect !== "undefined") {
      onSelect(selectedKeys);
    }
  };

  return (
    <Tree
      onSelect={onNodeSelect}
      treeData={tree}
      defaultExpandedKeys={expanded}
      defaultSelectedKeys={expanded}
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
