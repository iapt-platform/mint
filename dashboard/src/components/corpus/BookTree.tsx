import { useState, useEffect } from "react";
import { DownOutlined } from "@ant-design/icons";
import { Layout, Space, Tree } from "antd";
import { Select } from "antd";
import { Typography } from "antd";
import type { TreeProps } from "antd/es/tree";

import { get } from "../../request";

const { Text } = Typography;

const { Option } = Select;
interface IWidgetBookTree {
  root?: string;
  path?: string[];
}
const Widget = (prop: IWidgetBookTree) => {
  //Library foot bar
  //const intl = useIntl(); //i18n
  const defaultTreeData: NewTree[] = [];
  const [treeData, setTreeData] = useState(defaultTreeData);

  useEffect(() => {
    if (typeof prop.root !== "undefined") fetchBookTree(prop.root);
  }, [prop.root]);

  type OrgTree = {
    name: string;
    tag: string[];
    children: OrgTree[];
  };
  type NewTree = {
    title: string;
    key: string;
    tag: string[];
    children: NewTree[];
  };
  const onSelect: TreeProps["onSelect"] = (selectedKeys, info) => {
    //let aaa: NewTree = info.node;
    //console.log("selected", aaa.tag);
  };

  function fetchBookTree(value: string) {
    function treeMap(params: OrgTree): NewTree {
      return {
        title: params.name,
        key: params.tag.join(),
        tag: params.tag,
        children: Array.isArray(params.children)
          ? params.children.map(treeMap)
          : [],
      };
    }
    get(`/v2/palibook/${value}`).then((response) => {
      const myJson = response as unknown as OrgTree[];
      let newTree = myJson.map(treeMap);
      setTreeData(newTree);
    });
  }
  const handleChange = (value: string) => {
    console.log(`selected ${value}`);
    fetchBookTree(value);
  };

  // TODO
  return (
    <Layout>
      <Space>
        <Text>目录风格</Text>
        <Select
          defaultValue={prop.root}
          loading={false}
          onChange={handleChange}
        >
          <Option value="defualt">Defualt</Option>
          <Option value="cscd">CSCD</Option>
        </Select>
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
