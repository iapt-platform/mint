import { useState, useEffect } from "react";
import { DownOutlined } from "@ant-design/icons";
import { Layout, Space, Tree } from "antd";
import { Typography } from "antd";
import type { TreeProps } from "antd/es/tree";

import { get } from "../../request";
import { useNavigate } from "react-router-dom";
import TocStyleSelect from "./TocStyleSelect";
import { IPaliBookListResponse } from "../api/Corpus";
import { ITocTree } from "./BookTreeList";
import { PaliToEn } from "../../utils";

const { Text } = Typography;

interface IWidgetBookTree {
  root?: string;
  path?: string[];
  onChange?: Function;
}
const Widget = ({ root = "default", path, onChange }: IWidgetBookTree) => {
  //Library foot bar
  //const intl = useIntl(); //i18n
  const navigate = useNavigate();

  const [treeData, setTreeData] = useState<ITocTree[]>([]);

  useEffect(() => {
    if (typeof root !== "undefined") fetchBookTree(root);
  }, [root]);

  const onSelect: TreeProps["onSelect"] = (selectedKeys, info) => {
    //let aaa: NewTree = info.node;
    const node: ITocTree = info.node as unknown as ITocTree;
    console.log("tree selected", selectedKeys, node.path);
    if (typeof onChange !== "undefined" && selectedKeys.length > 0) {
      onChange(selectedKeys[0], node.path);
    }
  };

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
  const handleChange = (value: string) => {
    console.log(`selected ${value}`);
    localStorage.setItem("pali_path_root", value);
    navigate("/palicanon/list/" + value);
    fetchBookTree(value);
  };

  // TODO
  return (
    <Layout>
      <Space>
        <Text>目录风格</Text>
        <TocStyleSelect style={root} onChange={handleChange} />
      </Space>
      <Tree
        showLine
        switcherIcon={<DownOutlined />}
        defaultExpandedKeys={["sutta"]}
        onSelect={onSelect}
        treeData={treeData}
      />
    </Layout>
  );
};

export default Widget;
