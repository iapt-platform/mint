import React, { useState, useCallback } from "react";
import { message, Modal, Tree } from "antd";
import type { DataNode, TreeProps } from "antd/es/tree";
import type { Key } from "antd/lib/table/interface";
import { DeleteOutlined, SaveOutlined } from "@ant-design/icons";
import { FileAddOutlined, LinkOutlined } from "@ant-design/icons";
import { Button, Divider, Space } from "antd";
import { useIntl } from "react-intl";
import { randomString } from "../../../utils";
import EditableTreeNode from "./EditableTreeNode";

export interface TreeNodeData {
  key: string;
  id: string;
  title: string | React.ReactNode;
  title_text?: string;
  icon?: React.ReactNode;
  children: TreeNodeData[];
  status?: number;
  deletedAt?: string | null;
  level: number;
}

export type ListNodeData = {
  key: string;
  title: string | React.ReactNode;
  title_text?: string;
  level: number;
  status?: number;
  children?: number;
  deletedAt?: string | null;
};

let tocActivePath: TreeNodeData[] = [];

function tocGetTreeData(articles: ListNodeData[], active = "") {
  const treeData = [];
  const treeParents = [];

  const rootNode: TreeNodeData = {
    key: randomString(),
    id: "0",
    title: "root",
    title_text: "root",
    level: 0,
    children: [],
  };
  treeData.push(rootNode);
  let lastInsNode: TreeNodeData = rootNode;

  let iCurrLevel = 0;
  const keys: string[] = [];

  for (let index = 0; index < articles.length; index++) {
    const element = articles[index];

    const newNode: TreeNodeData = {
      key: randomString(),
      id: element.key,
      title: element.title,
      title_text: element.title_text,
      children: [],
      icon: keys.includes(element.key) ? <LinkOutlined /> : undefined,
      status: element.status,
      level: element.level,
      deletedAt: element.deletedAt,
    };

    if (!keys.includes(element.key)) {
      keys.push(element.key);
    }

    if (newNode.level > iCurrLevel) {
      treeParents.push(lastInsNode);
      lastInsNode.children.push(newNode);
    } else if (newNode.level === iCurrLevel) {
      treeParents[treeParents.length - 1].children.push(newNode);
    } else {
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
      for (let i = 1; i < treeParents.length; i++) {
        tocActivePath.push(treeParents[i]);
      }
    }
  }

  return treeData[0].children;
}

function treeToList(treeNode: TreeNodeData[]): ListNodeData[] {
  let iTocTreeCurrLevel = 1;
  const arrTocTree: ListNodeData[] = [];

  for (const iterator of treeNode) {
    getTreeNodeData(iterator);
  }

  function getTreeNodeData(node: TreeNodeData) {
    const children = node.children?.length ?? 0;
    arrTocTree.push({
      key: node.id,
      title: node.title,
      title_text: node.title_text,
      level: iTocTreeCurrLevel,
      children,
      deletedAt: node.deletedAt,
    });
    if (children > 0) {
      iTocTreeCurrLevel++;
      for (const iterator of node.children) {
        getTreeNodeData(iterator);
      }
      iTocTreeCurrLevel--;
    }
  }

  return arrTocTree;
}

interface IWidget {
  initValue?: ListNodeData[];
  value?: ListNodeData[];
  addFileButton?: React.ReactNode;
  addOnArticle?: TreeNodeData;
  updatedNode?: TreeNodeData;
  onChange?: (listTreeData?: ListNodeData[]) => void;
  onSelect?: (selectedKeys: React.Key[]) => void;
  onSave?: (listTreeData?: ListNodeData[]) => void;
  onAppend?: (parent: TreeNodeData) => Promise<TreeNodeData>;
  onTitleClick?: (
    e: React.MouseEvent<HTMLElement, MouseEvent>,
    node: TreeNodeData
  ) => void;
}

const EditableTreeWidget = ({
  initValue,
  value,
  addFileButton,
  addOnArticle,
  updatedNode,
  onChange,
  onSelect,
  onSave,
  onAppend,
  onTitleClick,
}: IWidget) => {
  const intl = useIntl();
  const isControlled = value !== undefined;

  const [checkKeys, setCheckKeys] = useState<string[]>([]);
  const [checkNodes, setCheckNodes] = useState<TreeNodeData[]>([]);

  // 非受控模式的内部树数据
  const [internalGData, setInternalGData] = useState<TreeNodeData[]>(() =>
    tocGetTreeData(initValue ?? [])
  );

  // 用 state 存上一次的 prop 值，用于在 render 阶段对比变化
  // 这是 React 官方文档推荐的派生 state 模式
  // https://react.dev/reference/react/useState#storing-information-from-previous-renders
  const [prevAddOnArticle, setPrevAddOnArticle] = useState<
    TreeNodeData | undefined
  >(undefined);
  const [prevUpdatedNode, setPrevUpdatedNode] = useState<
    TreeNodeData | undefined
  >(undefined);

  // 受控模式从 value 实时派生，非受控用内部 state
  const gData = isControlled ? tocGetTreeData(value) : internalGData;

  // 统一写入：非受控更新内部 state，始终触发 onChange
  const applyChange = useCallback(
    (newTree: TreeNodeData[]) => {
      if (!isControlled) {
        setInternalGData(newTree);
      }
      onChange?.(treeToList(newTree));
    },
    [isControlled, onChange]
  );

  // 处理 addOnArticle 变化：render 阶段对比 state，有变化则更新
  if (addOnArticle !== undefined && prevAddOnArticle !== addOnArticle) {
    setPrevAddOnArticle(addOnArticle);
    const newTree = [...gData, addOnArticle];
    if (!isControlled) {
      setInternalGData(newTree);
    }
    onChange?.(treeToList(newTree));
  }

  // 处理 updatedNode 变化
  if (updatedNode !== undefined && prevUpdatedNode !== updatedNode) {
    setPrevUpdatedNode(updatedNode);
    const newTree = [...gData];
    const update = (_node: TreeNodeData[]) => {
      _node.forEach((item, index, array) => {
        if (item.id === updatedNode.id) {
          array[index].title = updatedNode.title;
          array[index].title_text = updatedNode.title_text;
        } else {
          update(array[index].children);
        }
      });
    };
    update(newTree);
    if (!isControlled) {
      setInternalGData(newTree);
    }
    onChange?.(treeToList(newTree));
  }

  const appendNode = (key: string, node: TreeNodeData) => {
    const newTree = [...gData];
    const append = (_node: TreeNodeData[]) => {
      _node.forEach((item, index, array) => {
        if (item.key === key) {
          array[index].children.push(node);
        } else {
          append(array[index].children);
        }
      });
    };
    append(newTree);
    applyChange(newTree);
  };

  const onCheck: TreeProps["onCheck"] = (checkedKeys, info) => {
    setCheckKeys(checkedKeys as string[]);
    setCheckNodes(info.checkedNodes as TreeNodeData[]);
  };

  const onDragEnter: TreeProps["onDragEnter"] = () => {
    // expandedKeys 需要受控时在此设置
  };

  const onDrop: TreeProps["onDrop"] = (info) => {
    const dropKey = info.node.key;
    const dragKey = info.dragNode.key;
    const dropPos = info.node.pos.split("-");
    const dropPosition =
      info.dropPosition - Number(dropPos[dropPos.length - 1]);

    const loop = (
      data: DataNode[],
      key: React.Key,
      callback: (node: DataNode, i: number, data: DataNode[]) => void
    ) => {
      for (let i = 0; i < data.length; i++) {
        if (data[i].key === key) {
          return callback(data[i], i, data);
        }
        if (data[i].children) {
          loop(data[i].children!, key, callback);
        }
      }
    };

    const data = [...gData];
    let dragObj: DataNode;

    loop(data, dragKey, (item, index, arr) => {
      arr.splice(index, 1);
      dragObj = item;
    });

    if (!info.dropToGap) {
      loop(data, dropKey, (item) => {
        item.children = item.children || [];
        item.children.unshift(dragObj);
      });
    } else if (
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      ((info.node as any).props.children || []).length > 0 &&
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      (info.node as any).props.expanded &&
      dropPosition === 1
    ) {
      loop(data, dropKey, (item) => {
        item.children = item.children || [];
        item.children.unshift(dragObj);
      });
    } else {
      let ar: DataNode[] = [];
      let i: number;
      loop(data, dropKey, (_item, index, arr) => {
        ar = arr;
        i = index;
      });
      if (dropPosition === -1) {
        ar.splice(i!, 0, dragObj!);
      } else {
        ar.splice(i! + 1, 0, dragObj!);
      }
    }

    applyChange(data);
  };

  return (
    <>
      <Space>
        {addFileButton}
        <Button
          icon={<FileAddOutlined />}
          onClick={async () => {
            if (typeof onAppend !== "undefined") {
              const newNode: TreeNodeData = await onAppend({
                key: "",
                id: "",
                title: "",
                children: [],
                level: 0,
              });
              if (newNode) {
                const newTree = [...gData, newNode];
                applyChange(newTree);
                return true;
              } else {
                message.error("添加失败");
                return false;
              }
            }
            return false;
          }}
        >
          {intl.formatMessage({ id: "buttons.create" })}
        </Button>
        <Button
          icon={<DeleteOutlined />}
          danger
          disabled={checkKeys.length === 0}
          onClick={() => {
            const delTree = (node: TreeNodeData[]): boolean => {
              for (let index = 0; index < node.length; index++) {
                if (checkKeys.includes(node[index].key)) {
                  node.splice(index, 1);
                  return true;
                } else {
                  const cf = delTree(node[index].children);
                  if (cf) return cf;
                }
              }
              return false;
            };

            Modal.confirm({
              title: "从文集移除下列文章吗？(文章不会被删除)",
              content: (
                <>
                  {checkNodes.map((item, id) => (
                    <div key={id}>
                      {id + 1} {item.title}
                    </div>
                  ))}
                </>
              ),
              onOk() {
                const tmp = [...gData];
                delTree(tmp);
                applyChange(tmp);
              },
            });
          }}
        >
          {intl.formatMessage({ id: "buttons.remove" })}
        </Button>
        <Button
          icon={<SaveOutlined />}
          onClick={() => onSave?.(treeToList(gData))}
          type="primary"
        >
          {intl.formatMessage({ id: "buttons.save" })}
        </Button>
      </Space>
      <Divider />
      <Tree
        showLine
        showIcon
        checkable
        rootClassName="draggable-tree"
        draggable
        blockNode
        selectable={false}
        onDragEnter={onDragEnter}
        onDrop={onDrop}
        onCheck={onCheck}
        onSelect={(selectedKeys: Key[]) => {
          onSelect?.(selectedKeys);
        }}
        treeData={gData}
        titleRender={(node: TreeNodeData) => (
          <EditableTreeNode
            node={node}
            onAdd={async () => {
              if (typeof onAppend !== "undefined") {
                const newNode = await onAppend(node);
                if (newNode) {
                  appendNode(node.key, newNode);
                  return true;
                } else {
                  message.error("添加失败");
                  return false;
                }
              }
              return false;
            }}
            onTitleClick={(e: React.MouseEvent<HTMLElement, MouseEvent>) => {
              onTitleClick?.(e, node);
            }}
          />
        )}
      />
    </>
  );
};

export default EditableTreeWidget;
