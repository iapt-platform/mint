import React, { useState } from "react";
import { useEffect } from "react";
import { message, Tree } from "antd";
import type { DataNode, TreeProps } from "antd/es/tree";
import { Key } from "antd/lib/table/interface";
import { DeleteOutlined, SaveOutlined } from "@ant-design/icons";
import { Button, Divider, Space } from "antd";
import { useIntl } from "react-intl";
import EditableTreeNode from "./EditableTreeNode";

export interface TreeNodeData {
  key: string;
  title: string | React.ReactNode;
  children: TreeNodeData[];
  deletedAt?: string;
  level: number;
}
export type ListNodeData = {
  key: string;
  title: string | React.ReactNode;
  level: number;
  children?: number;
  deletedAt?: string;
};

var tocActivePath: TreeNodeData[] = [];
function tocGetTreeData(articles: ListNodeData[], active = "") {
  let treeData = [];

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
  for (let index = 0; index < articles.length; index++) {
    const element = articles[index];

    let newNode: TreeNodeData = {
      key: element.key,
      title: element.title,
      children: [],
      level: element.level,
      deletedAt: element.deletedAt,
    };
    /*
		if (active == element.article) {
			newNode["extraClasses"] = "active";
		}
*/
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
        tocActivePath.push(treeParents[index]);
      }
    }
  }
  return treeData[0].children;
}

function treeToList(treeNode: TreeNodeData[]): ListNodeData[] {
  let iTocTreeCurrLevel = 1;

  let arrTocTree: ListNodeData[] = [];

  for (const iterator of treeNode) {
    getTreeNodeData(iterator);
  }

  function getTreeNodeData(node: TreeNodeData) {
    let children = 0;
    if (typeof node.children != "undefined") {
      children = node.children.length;
    }
    arrTocTree.push({
      key: node.key,
      title: node.title,
      level: iTocTreeCurrLevel,
      children: children,
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
  treeData: ListNodeData[];
  addFileButton?: React.ReactNode;
  addOnArticle?: TreeNodeData;
  updatedNode?: TreeNodeData;
  onChange?: Function;
  onSelect?: Function;
  onSave?: Function;
  onAddFile?: Function;
  onAppend?: Function;
  onNodeEdit?: Function;
  onTitleClick?: Function;
}
const EditableTreeWidget = ({
  treeData,
  addFileButton,
  addOnArticle,
  updatedNode,
  onChange,
  onSelect,
  onSave,
  onAddFile,
  onAppend,
  onNodeEdit,
  onTitleClick,
}: IWidget) => {
  const intl = useIntl();

  const [gData, setGData] = useState<TreeNodeData[]>([]);
  const [listTreeData, setListTreeData] = useState<ListNodeData[]>();
  const [keys, setKeys] = useState<Key>("");

  useEffect(() => {
    if (typeof onChange !== "undefined") {
      onChange(listTreeData);
    }
  }, [listTreeData]);

  useEffect(() => {
    //找到节点并更新
    if (typeof updatedNode === "undefined") {
      return;
    }
    const update = (_node: TreeNodeData[]) => {
      _node.forEach((value, index, array) => {
        if (value.key === updatedNode.key) {
          array[index].title = updatedNode.title;
          console.log("key found");
          return;
        } else {
          update(array[index].children);
        }
        return;
      });
    };
    const newTree = [...gData];
    update(newTree);
    setGData(newTree);
    const list = treeToList(newTree);
    setListTreeData(list);
  }, [updatedNode]);

  const appendNode = (key: string, node: TreeNodeData) => {
    console.log("key", key);
    const append = (_node: TreeNodeData[]) => {
      _node.forEach((value, index, array) => {
        if (value.key === key) {
          array[index].children.push(node);
          console.log("key found");
          return;
        } else {
          append(array[index].children);
        }
        return;
      });
    };
    const newTree = [...gData];
    append(newTree);
    setGData(newTree);
    const list = treeToList(newTree);
    setListTreeData(list);
  };

  useEffect(() => {
    if (typeof addOnArticle === "undefined") {
      return;
    }
    console.log("add ", addOnArticle);

    const newTreeData = [...gData, addOnArticle];
    setGData(newTreeData);
    const list = treeToList(newTreeData);
    setListTreeData(list);
  }, [addOnArticle]);

  useEffect(() => {
    const data = tocGetTreeData(treeData);
    console.log("tree data", data);
    setGData(data);
  }, [treeData]);

  const onDragEnter: TreeProps["onDragEnter"] = (info) => {
    console.log(info);
    // expandedKeys 需要受控时设置
    // setExpandedKeys(info.expandedKeys)
  };

  const onDrop: TreeProps["onDrop"] = (info) => {
    console.log(info);
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

    // Find dragObject
    let dragObj: DataNode;
    loop(data, dragKey, (item, index, arr) => {
      arr.splice(index, 1);
      dragObj = item;
    });

    if (!info.dropToGap) {
      // Drop on the content
      loop(data, dropKey, (item) => {
        item.children = item.children || [];
        // where to insert 示例添加到头部，可以是随意位置
        item.children.unshift(dragObj);
      });
    } else if (
      ((info.node as any).props.children || []).length > 0 && // Has children
      (info.node as any).props.expanded && // Is expanded
      dropPosition === 1 // On the bottom gap
    ) {
      loop(data, dropKey, (item) => {
        item.children = item.children || [];
        // where to insert 示例添加到头部，可以是随意位置
        item.children.unshift(dragObj);
        // in previous version, we use item.children.push(dragObj) to insert the
        // item to the tail of the children
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
    setGData(data);
    const list = treeToList(data);
    setListTreeData(list);
  };

  return (
    <>
      <Space>
        {addFileButton}

        <Button
          icon={<DeleteOutlined />}
          danger
          onClick={() => {
            const delTree = (node: TreeNodeData[]): boolean => {
              for (let index = 0; index < node.length; index++) {
                if (node[index].key === keys) {
                  node.splice(index, 1);
                  return true;
                } else {
                  const cf = delTree(node[index].children);
                  if (cf) {
                    return cf;
                  }
                }
              }
              return false;
            };
            const tmp = [...gData];
            const find = delTree(tmp);

            console.log("delete", keys, find, tmp);
            setGData(tmp);
            const list = treeToList(tmp);
            setListTreeData(list);
          }}
        >
          {intl.formatMessage({ id: "buttons.remove" })}
        </Button>
        <Button
          icon={<SaveOutlined />}
          onClick={() => {
            if (typeof onSave !== "undefined") {
              onSave(listTreeData);
            }
          }}
          type="primary"
        >
          {intl.formatMessage({ id: "buttons.save" })}
        </Button>
      </Space>
      <Divider></Divider>
      <Tree
        rootClassName="draggable-tree"
        draggable
        blockNode
        onDragEnter={onDragEnter}
        onDrop={onDrop}
        onSelect={(selectedKeys: Key[]) => {
          if (selectedKeys.length > 0) {
            setKeys(selectedKeys[0]);
          } else {
            setKeys("");
          }
          if (typeof onSelect !== "undefined") {
            onSelect(selectedKeys);
          }
        }}
        treeData={gData}
        titleRender={(node: TreeNodeData) => {
          return (
            <EditableTreeNode
              node={node}
              onEdit={() => {
                if (typeof onNodeEdit !== "undefined") {
                  onNodeEdit(node.key);
                }
              }}
              onAdd={async () => {
                if (typeof onAppend !== "undefined") {
                  const newNode = await onAppend(node);
                  console.log("newNode", newNode);
                  if (newNode) {
                    appendNode(node.key, newNode);
                    return true;
                  } else {
                    message.error("添加失败");
                    return false;
                  }
                } else {
                  return false;
                }
              }}
              onTitleClick={(e: React.MouseEvent<HTMLElement, MouseEvent>) => {
                if (typeof onTitleClick !== "undefined") {
                  onTitleClick(e, node);
                }
              }}
            />
          );
        }}
      />
    </>
  );
};

export default EditableTreeWidget;
