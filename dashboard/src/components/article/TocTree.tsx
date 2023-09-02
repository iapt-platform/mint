import { Tree, Typography } from "antd";
import { useEffect, useState } from "react";

import type { ListNodeData } from "./EditableTree";
import PaliText from "../template/Wbw/PaliText";
import { Key } from "antd/lib/table/interface";

const { Text } = Typography;

export interface TreeNodeData {
  key: string;
  title: string | React.ReactNode;
  children?: TreeNodeData[];
  level: number;
  deletedAt?: string;
}

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
      deletedAt: element.deletedAt,
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
  expandedKeys?: Key[];
  selectedKeys?: Key[];
  onSelect?: Function;
}

const TocTreeWidget = ({
  treeData,
  expandedKeys,
  selectedKeys,
  onSelect,
}: IWidgetTocTree) => {
  const [tree, setTree] = useState<TreeNodeData[]>();
  const [expanded, setExpanded] = useState<Key[]>();
  const [selected, setSelected] = useState<Key[]>();

  useEffect(() => {
    console.log("new tree data", treeData);
    if (treeData && treeData.length > 0) {
      const data = tocGetTreeData(treeData);
      setTree(data);
      console.log("create tree", treeData.length);
    } else {
      setTree([]);
    }
  }, [treeData]);

  useEffect(() => setSelected(selectedKeys), [selectedKeys]);
  useEffect(() => setExpanded(expandedKeys), [expandedKeys]);

  return (
    <Tree
      treeData={tree}
      expandedKeys={expanded}
      selectedKeys={selected}
      onExpand={(expandedKeys: Key[]) => {
        setExpanded(expandedKeys);
      }}
      onSelect={(selectedKeys: Key[]) => {
        setSelected(selectedKeys);
        if (typeof onSelect !== "undefined") {
          onSelect(selectedKeys);
        }
      }}
      blockNode
      titleRender={(node: TreeNodeData) => {
        const currNode =
          typeof node.title === "string" ? (
            <PaliText text={node.title} />
          ) : (
            <>{node.title}</>
          );
        return node.deletedAt ? (
          <Text delete disabled>
            {currNode}
          </Text>
        ) : (
          <>{currNode}</>
        );
      }}
    />
  );
};

export default TocTreeWidget;
