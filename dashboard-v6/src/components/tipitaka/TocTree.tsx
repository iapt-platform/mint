import { Tree, Typography } from "antd";
import { useMemo, useState } from "react";

import type { Key } from "antd/lib/table/interface";
import { randomString } from "../../utils";
import type { DataNode, EventDataNode } from "antd/es/tree";
import type { ListNodeData } from "../article/components/EditableTree";
import PaliText from "../general/PaliText";

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
      treeParents.push(lastInsNode);
      if (typeof lastInsNode.children === "undefined") {
        lastInsNode.children = [];
      }
      lastInsNode.children.push(newNode);
    } else if (newNode.level === iCurrLevel) {
      const parentNode = treeParents[treeParents.length - 1];
      if (typeof parentNode !== "undefined") {
        if (typeof parentNode.children === "undefined") {
          parentNode.children = [];
        }
        parentNode.children.push(newNode);
      }
    } else {
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
  onLoad?: (key: string) => string;
}

const TocTreeWidget = ({
  treeData,
  expandedKeys,
  selectedKeys,
  onSelect,
  onClick,
}: IWidgetTocTree) => {
  // 用于记录用户手动展开/收起的状态
  const [manualExpanded, setManualExpanded] = useState<Key[] | undefined>();

  const [tree, keyIdMap] = useMemo(() => {
    if (treeData && treeData.length > 0) {
      const [data, idMap] = tocGetTreeData(treeData, "");
      return [data, idMap];
    } else {
      return [[], undefined];
    }
  }, [treeData]);

  // 用 useMemo 替代 useEffect + setState，避免级联渲染
  const selected = useMemo(() => {
    if (!keyIdMap) return undefined;
    return selectedKeys?.map((item) => {
      const mapIndex = keyIdMap.findIndex((value) => value.id === item);
      return mapIndex !== -1 ? keyIdMap[mapIndex].key : "";
    });
  }, [keyIdMap, selectedKeys]);

  const expandedFromProps = useMemo(() => {
    if (!keyIdMap) return undefined;
    return expandedKeys?.map((item) => {
      const mapIndex = keyIdMap.findIndex((value) => value.id === item);
      return mapIndex !== -1 ? keyIdMap[mapIndex].key : "";
    });
  }, [expandedKeys, keyIdMap]);

  // 优先使用用户手动操作的展开状态，否则使用 props 传入的
  const expanded = manualExpanded ?? expandedFromProps;

  return (
    <Tree
      treeData={tree}
      selectedKeys={selected}
      expandedKeys={expanded}
      autoExpandParent
      onExpand={(expandedKeys: Key[]) => {
        setManualExpanded(expandedKeys);
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
        if (typeof onSelect !== "undefined") {
          const selectedId = keyIdMap
            ?.filter((value) => selectedKeys.includes(value.key))
            .map((item) => item.id);
          onSelect(selectedId);
        }
      }}
      blockNode
      titleRender={(node: DataNode) => {
        const treeNode = node as TreeNodeData; // 类型断言
        const currNode =
          typeof treeNode.title === "string" ? (
            treeNode.title === "" ? (
              "[unnamed]"
            ) : (
              <PaliText
                textType={treeNode.status === 10 ? "secondary" : undefined}
                text={treeNode.title}
              />
            )
          ) : (
            treeNode.title
          );

        return (
          <Text
            delete={treeNode.deletedAt ? true : false}
            disabled={treeNode.deletedAt ? true : false}
            type={treeNode.status === 10 ? "secondary" : undefined}
          >
            {currNode}
          </Text>
        );
      }}
    />
  );
};

export default TocTreeWidget;
