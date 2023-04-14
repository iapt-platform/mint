import { useState, useEffect, Key } from "react";
import { DownOutlined } from "@ant-design/icons";
import { Button, Space, Switch, Tree } from "antd";
import { Typography } from "antd";

import { get } from "../../request";
import TocStyleSelect from "./TocStyleSelect";
import { IPaliBookListResponse } from "../api/Corpus";
import { ITocTree } from "./BookTreeList";
import { PaliToEn } from "../../utils";
import PaliText from "../template/Wbw/PaliText";

const { Text } = Typography;

interface IWidgetBookTree {
  root?: string;
  path?: string[];
  multiSelect?: boolean;
  multiSelectable?: boolean;
  onChange?: Function;
  onSelect?: Function;
  onRootChange?: Function;
}
const Widget = ({
  root = "default",
  path,
  multiSelect = false,
  multiSelectable = true,
  onChange,
  onSelect,
  onRootChange,
}: IWidgetBookTree) => {
  const [treeData, setTreeData] = useState<ITocTree[]>([]);
  const [selectedKeys, setSelectedKeys] = useState<Key[]>([]);
  const [isMultiSelect, setIsMultiSelect] = useState(multiSelect);
  useEffect(() => {
    setIsMultiSelect(multiSelect);
  }, [multiSelect]);
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
    <Space direction="vertical" style={{ padding: 10, width: "100%" }}>
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

      <Space style={{ display: "flex", justifyContent: "space-between" }}>
        <Button
          onClick={() => {
            setSelectedKeys([]);
            if (typeof onChange !== "undefined") {
              onChange([], []);
            }
          }}
        >
          清除选择
        </Button>
        {multiSelectable ? (
          <Space>
            {"多选"}
            <Switch
              size="small"
              defaultChecked={multiSelect}
              onChange={(checked) => {
                setIsMultiSelect(checked);
              }}
            />
          </Space>
        ) : undefined}
      </Space>

      <Tree
        selectedKeys={selectedKeys}
        multiple={isMultiSelect}
        showLine
        switcherIcon={<DownOutlined />}
        defaultExpandedKeys={["sutta"]}
        onCheck={(checkedKeysValue, info) => {
          console.log("onCheck", checkedKeysValue);
          //setCheckedKeys(checkedKeysValue);
        }}
        onSelect={(selectedKeys, info) => {
          console.log("tree selected", selectedKeys, info);
          setSelectedKeys(selectedKeys);
          //let aaa: NewTree = info.node;
          const node: ITocTree = info.node as unknown as ITocTree;

          if (typeof onChange !== "undefined") {
            onChange(selectedKeys, node.path);
          }
          if (typeof onSelect !== "undefined") {
            onSelect(selectedKeys.length > 0 ? selectedKeys[0] : undefined);
          }
        }}
        treeData={treeData}
        titleRender={(node: ITocTree) => {
          return <PaliText text={node.title} />;
        }}
      />
    </Space>
  );
};

export default Widget;
