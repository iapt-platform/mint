import { useIntl } from "react-intl";
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
const BookTreeWidget = ({
  root,
  path,
  multiSelect = false,
  multiSelectable = true,
  onChange,
  onSelect,
  onRootChange,
}: IWidgetBookTree) => {
  const intl = useIntl();
  const [treeData, setTreeData] = useState<ITocTree[]>([]);
  const [selectedKeys, setSelectedKeys] = useState<Key[]>([]);
  const [isMultiSelect, setIsMultiSelect] = useState(multiSelect);
  const [currTocStyle, setCurrTocStyle] = useState<string>();

  useEffect(() => {
    setIsMultiSelect(multiSelect);
  }, [multiSelect]);

  useEffect(() => {
    let tocStyle = "default";
    if (typeof root !== "undefined") {
      tocStyle = root;
    } else {
      const store = localStorage.getItem("pali_path_root");
      if (store) {
        tocStyle = store;
      }
    }
    fetchBookTree(tocStyle);
    setCurrTocStyle(tocStyle);
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

  return (
    <Space direction="vertical" style={{ padding: 10, width: "100%" }}>
      <Space style={{ display: "flex", justifyContent: "space-between" }}>
        <Text>目录</Text>
        <TocStyleSelect
          style={currTocStyle}
          onChange={(value: string) => {
            console.log(`selected ${value}`);
            localStorage.setItem("pali_path_root", value);
            if (typeof onRootChange !== "undefined") {
              onRootChange(value);
            }
            setCurrTocStyle(value);
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
          {intl.formatMessage({
            id: "buttons.remove.selected",
          })}
        </Button>
        {multiSelectable ? (
          <Space>
            <Text>
              {intl.formatMessage({
                id: "buttons.multiple.select",
              })}
            </Text>
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

export default BookTreeWidget;
