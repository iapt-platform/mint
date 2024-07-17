import { Button, Tree } from "antd";
import { Key } from "antd/lib/table/interface";
import { useState } from "react";

interface DataNode {
  title: string;
  key: string;
  level?: number;
  children?: DataNode[];
  deletedAt?: string | null;
  isLeaf?: boolean;
}

const initTreeData: DataNode[] = [
  { title: "Expand to load", key: "0" },
  { title: "Expand to load", key: "1" },
  { title: "Tree Node", key: "2", isLeaf: true },
];

// It's just a simple demo. You can use tree map to optimize update perf.
const updateTreeData = (
  list: DataNode[],
  key: React.Key,
  children: DataNode[]
): DataNode[] =>
  list.map((node) => {
    if (node.key === key) {
      return {
        ...node,
        children,
      };
    }
    if (node.children) {
      return {
        ...node,
        children: updateTreeData(node.children, key, children),
      };
    }
    return node;
  });

const Widget = () => {
  const [selectedKeys, setSelectedKeys] = useState<Key[]>();
  const [expandedKeys, setExpandedKeys] = useState<Key[]>();
  const [treeData, setTreeData] = useState<DataNode[]>(initTreeData);

  const onLoadData = ({ key, children }: any) =>
    new Promise<void>((resolve) => {
      if (children) {
        resolve();
        return;
      }
      setTimeout(() => {
        setTreeData((origin) =>
          updateTreeData(origin, key, [
            { title: "Child Node", key: `${key}-0` },
            { title: "Child Node", key: `${key}-1` },
          ])
        );

        resolve();
      }, 1000);
    });
  const onLoad = ({ key, children }: any) =>
    new Promise<void>((resolve) => {
      if (children) {
        resolve();
        return;
      }
      setTimeout(() => {
        setTreeData((origin) =>
          updateTreeData(origin, key, [
            { title: "Child Node", key: `${key}-0` },
            { title: "Child Node", key: `${key}-1` },
          ])
        );

        resolve();
      }, 1000);
    });
  return (
    <>
      <Button
        onClick={() => {
          setTreeData((origin) => {
            return [...origin, { title: "title3", key: "title3" }];
          });
        }}
      >
        add
      </Button>
      <Button
        onClick={() => {
          setExpandedKeys(["title1"]);
          setSelectedKeys(["title1-2"]);
        }}
      >
        expand
      </Button>
      <Tree
        treeData={treeData}
        onSelect={(selectedKeys: Key[]) => setSelectedKeys(selectedKeys)}
        loadData={onLoadData}
      />
    </>
  );
};

export default Widget;
