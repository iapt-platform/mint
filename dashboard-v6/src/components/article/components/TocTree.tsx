import { Tree, Typography } from "antd";
import { useMemo, useState } from "react";

import type { Key } from "antd/lib/table/interface";

import type { DataNode, EventDataNode } from "antd/es/tree";
import type { ListNodeData } from "./EditableTree";
import { randomString } from "../../../utils";
import PaliText from "../../general/PaliText";

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
  const treeData: TreeNodeData[] = [];
  let tocActivePath: TreeNodeData[] = [];
  const treeParents = [];
  const rootNode: TreeNodeData = {
    key: randomString(),
    id: "0",
    title: "root",
    level: 0,
    children: [],
  };
  const idMap: IIdMap[] = [];
  treeData.push(rootNode);
  let lastInsNode: TreeNodeData = rootNode;

  let iCurrLevel = 0;
  for (let index = 0; index < listData.length; index++) {
    const element = listData[index];
    const newNode: TreeNodeData = {
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

interface IWidgetTocTree {
  treeData?: ListNodeData[];
  expandedKeys?: Key[];
  selectedKeys?: Key[];
  onSelect?: (selectedId?: string[]) => void;
  onClick?: (
    selectedId: string,
    e: React.MouseEvent<HTMLSpanElement, MouseEvent>
  ) => void;
}

const TocTreeWidget = ({
  treeData,
  expandedKeys,
  selectedKeys,
  onSelect,
  onClick,
}: IWidgetTocTree) => {
  // 仅保留真正需要组件内部管理的交互状态
  const [localExpanded, setLocalExpanded] = useState<Key[]>();

  // 用 useMemo 替代 useEffect + setState 派生 tree 和 keyIdMap
  const [tree, keyIdMap] = useMemo(() => {
    if (treeData && treeData.length > 0) {
      return tocGetTreeData(treeData, "");
    }
    return [[], []];
  }, [treeData]);

  // 用 useMemo 派生 selected keys（props id → 内部随机 key 的映射）
  const selected = useMemo(() => {
    return selectedKeys?.map((item) => {
      const found = keyIdMap.find((value) => value.id === item);
      return found?.key ?? "";
    });
  }, [keyIdMap, selectedKeys]);

  // expanded 同理，但需要合并外部传入和本地交互，优先使用本地状态
  const expanded = useMemo(() => {
    if (localExpanded !== undefined) return localExpanded;
    return expandedKeys?.map((item) => {
      const found = keyIdMap.find((value) => value.id === item);
      return found?.key ?? "";
    });
  }, [expandedKeys, keyIdMap, localExpanded]);

  return (
    <Tree
      treeData={tree}
      selectedKeys={selected}
      expandedKeys={expanded}
      autoExpandParent
      onExpand={(keys: Key[]) => {
        setLocalExpanded(keys); // 用户手动展开/收起，只需本地管理
      }}
      onClick={(
        e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
        node: EventDataNode<DataNode>
      ) => {
        if (typeof onClick !== "undefined") {
          const selectedId = keyIdMap.find(
            (value) => node.key === value.key
          )?.id;
          if (selectedId) {
            onClick(selectedId, e);
          }
        }
      }}
      onSelect={(keys: Key[]) => {
        if (typeof onSelect !== "undefined") {
          const selectedId = keyIdMap
            .filter((value) => keys.includes(value.key))
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
