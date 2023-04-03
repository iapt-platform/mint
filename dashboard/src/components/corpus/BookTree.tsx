import { useState, useEffect } from "react";
import { DownOutlined } from "@ant-design/icons";
import { Space, Tree } from "antd";
import { Typography } from "antd";

import { get } from "../../request";
import TocStyleSelect from "./TocStyleSelect";
import { IPaliBookListResponse } from "../api/Corpus";
import { ITocTree } from "./BookTreeList";
import { PaliToEn } from "../../utils";

const { Text } = Typography;

interface IWidgetBookTree {
  root?: string;
  path?: string[];
  multiSelect?: boolean;
  onChange?: Function;
  onSelect?: Function;
  onRootChange?: Function;
}
const Widget = ({
  root = "default",
  path,
  multiSelect = false,
  onChange,
  onSelect,
  onRootChange,
}: IWidgetBookTree) => {
  const [treeData, setTreeData] = useState<ITocTree[]>([]);

  useEffect(() => {
    if (typeof root !== "undefined") fetchBookTree(root);
  }, [root]);

  function fetchBookTree(value: string) {
    function treeMap(params: IPaliBookListResponse): ITocTree {
      return {
        title: params.name,
        dir: PaliToEn(params.name),
        key: params.tag.join(),
        tag: params.tag,
        children: Array.isArray(params.children)
          ? params.children.map(treeMap)
          : [],
      };
    }
    function setPathToNode(nodes: ITocTree[], path: string[]) {
      for (let node of nodes) {
        node.path = [...path, node.title];
        setPathToNode(node.children, node.path);
      }
    }
    get<IPaliBookListResponse[]>(`/v2/palibook/${value}`).then((json) => {
      let newTree: ITocTree[] = json.map(treeMap);
      setPathToNode(newTree, []);
      console.log("root", newTree);
      setTreeData(newTree);
    });
  }

  // TODO
  return (
    <Space direction="vertical" style={{ padding: 10 }}>
      <Space style={{ display: "flex", justifyContent: "space-between" }}>
        <Text>目录</Text>
        <TocStyleSelect
          style={root}
          onChange={(value: string) => {
            console.log(`selected ${value}`);
            localStorage.setItem("pali_path_root", value);
            if (typeof onRootChange !== "undefined") {
              onRootChange(value);
            }
            fetchBookTree(value);
          }}
        />
      </Space>
      <Tree
        multiple={multiSelect}
        showLine
        switcherIcon={<DownOutlined />}
        defaultExpandedKeys={["sutta"]}
        onSelect={(selectedKeys, info) => {
          //let aaa: NewTree = info.node;
          const node: ITocTree = info.node as unknown as ITocTree;
          console.log("tree selected", selectedKeys, node.path);
          if (typeof onChange !== "undefined") {
            onChange(selectedKeys, node.path);
          }
          if (typeof onSelect !== "undefined") {
            onSelect(selectedKeys.length > 0 ? selectedKeys[0] : undefined);
          }
        }}
        treeData={treeData}
      />
    </Space>
  );
};

export default Widget;
