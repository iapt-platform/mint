import { Tree, Typography } from "antd";
import { useEffect, useState } from "react";

import type { ListNodeData } from "./EditableTree";
import PaliText from "../template/Wbw/PaliText";
import { Key } from "antd/lib/table/interface";
import { randomString } from "../../utils";
import { DataNode, EventDataNode } from "antd/es/tree";

const { Text } = Typography;

interface IIdMap {
  key: string;
  id: string;
}
export interface TreeNodeData {
  key: string;
  id: string;
  title: string | React.ReactNode;
  isLeaf?: boolean;
  children?: TreeNodeData[];
  level: number;
  status?: number;
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
      isLeaf: element.children === 0,
      title: element.title,
      level: element.level,
      status: element.status,
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

// It's just a lazy load simple demo. You can use tree map to optimize update perf.
const updateTreeData = (
  list: TreeNodeData[],
  key: React.Key,
  children: TreeNodeData[]
): TreeNodeData[] => {
  return list.map((node) => {
    if (node.key === key) {
      console.debug("updateTreeData found", key, node);
      node.children = children;
      return node;
    }

    if (node.children) {
      return {
        ...node,
        children: updateTreeData(node.children, key, children),
      };
    }

    return node;
  });
};

interface IWidgetTocTree {
  treeData?: ListNodeData[];
  expandedKeys?: Key[];
  selectedKeys?: Key[];
  onSelect?: Function;
  onClick?: Function;
  onLoad?: (key: string) => string;
}

const TocTreeWidget = ({
  treeData,
  expandedKeys,
  selectedKeys,
  onSelect,
  onClick,
  onLoad,
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

  const onLoadData = ({ key, children }: any) =>
    new Promise<void>((resolve) => {
      if (children) {
        resolve();
        return;
      }
      if (typeof onLoad === "undefined") {
        resolve();
        return;
      }
      const url = onLoad(key);

      setTimeout(() => {
        setTree((origin) => {
          if (!origin) {
            return origin;
          }
          return updateTreeData(origin, key, [
            {
              title: "Child Node",
              key: randomString(),
              id: `${key}-0`,
              level: 2,
            },
            {
              title: "Child Node",
              key: randomString(),
              id: `${key}-1`,
              level: 2,
            },
          ]);
        });

        resolve();
      }, 1000);
    });

  return (
    <Tree
      treeData={tree}
      selectedKeys={selected}
      expandedKeys={expanded}
      autoExpandParent
      loadData={onLoadData}
      onExpand={(expandedKeys: Key[]) => {
        setExpanded(expandedKeys);
      }}
      onClick={(
        e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
        node: EventDataNode<DataNode>
      ) => {
        if (typeof onClick !== "undefined") {
          const selectedId = keyIdMap?.find(
            (value) => node.key === value.key
          )?.id;
          if (selectedId) {
            onClick(selectedId, e);
          }
        }
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
              <PaliText
                textType={node.status === 10 ? "secondary" : undefined}
                text={node.title}
              />
            )
          ) : (
            node.title
          );

        return (
          <Text
            delete={node.deletedAt ? true : false}
            disabled={node.deletedAt ? true : false}
            type={node.status === 10 ? "secondary" : undefined}
          >
            {currNode}
          </Text>
        );
      }}
    />
  );
};

export default TocTreeWidget;
