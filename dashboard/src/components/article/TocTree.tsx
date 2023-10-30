import { Tree, Typography } from "antd";
import { useEffect, useState } from "react";

import type { ListNodeData } from "./EditableTree";
import PaliText from "../template/Wbw/PaliText";
import { Key } from "antd/lib/table/interface";
import { randomString } from "../../utils";

const { Text } = Typography;

interface IIdMap {
  key: string;
  id: string;
}
export interface TreeNodeData {
  key: string;
  id: string;
  title: string | React.ReactNode;
  children?: TreeNodeData[];
  level: number;
  deletedAt?: string | null;
}

function tocGetTreeData(
  listData: ListNodeData[],
  active = ""
): [TreeNodeData[] | undefined, IIdMap[]] {
  let treeData: TreeNodeData[] = [];
  let tocActivePath: TreeNodeData[] = [];
  let treeParents = [];
  let rootNode: TreeNodeData = {
    key: randomString(),
    id: "0",
    title: "root",
    level: 0,
    children: [],
  };
  let idMap: IIdMap[] = [];
  treeData.push(rootNode);
  let lastInsNode: TreeNodeData = rootNode;

  let iCurrLevel = 0;
  for (let index = 0; index < listData.length; index++) {
    const element = listData[index];
    let newNode: TreeNodeData = {
      key: randomString(),
      id: element.key,
      title: element.title,
      level: element.level,
      deletedAt: element.deletedAt,
    };
    idMap.push({
      key: newNode.key,
      id: newNode.id,
    });
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

  return [treeData[0].children, idMap];
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
  const [keyIdMap, setKeyIdMap] = useState<IIdMap[]>();

  useEffect(() => {
    if (treeData && treeData.length > 0) {
      const [data, idMap] = tocGetTreeData(treeData, "");
      setTree(data);
      setKeyIdMap(idMap);
      console.log(" tree data", data);
    } else {
      setTree([]);
    }
  }, [treeData]);

  useEffect(() => {
    if (!keyIdMap) {
      return;
    }
    const realKey = selectedKeys?.map((item) => {
      const mapIndex = keyIdMap?.findIndex((value) => value.id === item);
      if (mapIndex !== -1) {
        return keyIdMap[mapIndex].key;
      } else {
        return "";
      }
    });
    console.log("realKey", realKey);
    setSelected(realKey);
  }, [keyIdMap, selectedKeys]);

  useEffect(() => {
    if (!keyIdMap) {
      return;
    }
    const realKey = expandedKeys?.map((item) => {
      const mapIndex = keyIdMap?.findIndex((value) => value.id === item);
      if (mapIndex !== -1) {
        return keyIdMap[mapIndex].key;
      } else {
        return "";
      }
    });
    console.log("realKey", realKey);
    setExpanded(realKey);
  }, [expandedKeys, keyIdMap]);

  console.log("selected", selected);
  return (
    <Tree
      treeData={tree}
      selectedKeys={selected}
      expandedKeys={expanded}
      autoExpandParent
      onExpand={(expandedKeys: Key[]) => {
        setExpanded(expandedKeys);
      }}
      onSelect={(selectedKeys: Key[]) => {
        setSelected(selectedKeys);
        if (typeof onSelect !== "undefined") {
          const selectedId = keyIdMap
            ?.filter((value) => selectedKeys.includes(value.key))
            .map((item) => item.id);
          onSelect(selectedId);
        }
      }}
      blockNode
      titleRender={(node: TreeNodeData) => {
        const currNode =
          typeof node.title === "string" ? (
            node.title === "" ? (
              "[unnamed]"
            ) : (
              <PaliText text={node.title} />
            )
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
