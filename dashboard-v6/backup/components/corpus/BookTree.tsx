import { useIntl } from "react-intl";
import { useState, useEffect, type Key } from "react";
import { DownOutlined } from "@ant-design/icons";
import {
  Badge,
  Button,
  Flex,
  Switch,
  Tree,
  Typography,
  Space,
  Skeleton,
} from "antd";
import { get } from "../../request";
import TocStyleSelect from "./TocStyleSelect";
import type { IPaliBookListResponse } from "../../api/Corpus";
import type { ITocTree } from "./BookTreeList";
import { PaliToEn } from "../../utils";
import PaliText from "../template/Wbw/PaliText";
import type { IFtsData } from "../fts/FtsBookList";
import type { EventDataNode } from "antd/lib/tree";

const { Text } = Typography;

interface IWidgetBookTree {
  root?: string;
  path?: string[];
  multiSelect?: boolean;
  multiSelectable?: boolean;
  books?: IFtsData[];
  onChange?: (selectedKeys: Key[], path?: string[]) => void;
  onSelect?: (key?: Key) => void;
  onRootChange?: (value: string) => void;
}

const BookTreeWidget = ({
  root,
  multiSelect = false,
  multiSelectable = true,
  books,
  onChange,
  onSelect,
  onRootChange,
}: IWidgetBookTree) => {
  const intl = useIntl();
  const [treeData, setTreeData] = useState<ITocTree[]>([]);
  const [selectedKeys, setSelectedKeys] = useState<Key[]>([]);
  const [isMultiSelect, setIsMultiSelect] = useState(multiSelect);
  const [currTocStyle, setCurrTocStyle] = useState<string>();
  const [loading, setLoading] = useState<boolean>(false);

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

  const fetchBookTree = async (value: string) => {
    setLoading(true);
    try {
      const treeMap = (params: IPaliBookListResponse): ITocTree => ({
        title: params.name,
        dir: PaliToEn(params.name),
        key: params.tag.join(),
        tag: params.tag,
        children: Array.isArray(params.children)
          ? params.children.map(treeMap)
          : [],
      });

      const setPathToNode = (nodes: ITocTree[], path: string[]) => {
        nodes.forEach((node) => {
          node.path = [...path, node.title];
          setPathToNode(node.children, node.path);
        });
      };

      const json = await get<IPaliBookListResponse[]>(
        `/v2/pali-book-category/${value}`
      );
      const newTree: ITocTree[] = json.map(treeMap);
      setPathToNode(newTree, []);
      console.log("root", newTree);
      setTreeData(newTree);
    } catch (error) {
      console.error("获取目录树失败:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleClearSelection = () => {
    setSelectedKeys([]);
    if (typeof onChange !== "undefined") {
      onChange([], []);
    }
  };

  const handleSelect = (
    selectedKeys: Key[],
    info: {
      selected: boolean;
      selectedNodes: ITocTree[];
      node: EventDataNode<ITocTree>;
      event: string;
    }
  ) => {
    console.log("tree selected", selectedKeys, info);
    setSelectedKeys(selectedKeys);
    const node: ITocTree = info.node;

    if (typeof onChange !== "undefined") {
      onChange(selectedKeys, node.path);
    }
    if (typeof onSelect !== "undefined") {
      onSelect(selectedKeys.length > 0 ? selectedKeys[0] : undefined);
    }
  };

  const titleRender = (node: ITocTree) => {
    //标签数量
    const tags = books?.filter((book) => {
      return node.tag.every((el) => {
        return book.tags?.map((item) => item.name).includes(el);
      });
    });
    const count = tags?.length;
    return (
      <Space>
        <PaliText text={node.title} />
        {count ? (
          <Badge size="small" color="gray" count={count} dot={false} />
        ) : null}
      </Space>
    );
  };

  return (
    <Flex vertical style={{ padding: 10, width: "100%" }} gap="middle">
      <Flex justify="space-between" align="center">
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
      </Flex>

      <Flex justify="space-between" align="center">
        <Button onClick={handleClearSelection}>
          {intl.formatMessage({
            id: "buttons.remove.selected",
          })}
        </Button>
        {multiSelectable ? (
          <Flex align="center" gap="small">
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
          </Flex>
        ) : null}
      </Flex>
      {loading ? (
        <Skeleton />
      ) : (
        <Tree
          selectedKeys={selectedKeys}
          multiple={isMultiSelect}
          showLine
          switcherIcon={<DownOutlined />}
          defaultExpandedKeys={["sutta"]}
          onSelect={handleSelect}
          treeData={treeData}
          titleRender={titleRender}
        />
      )}
    </Flex>
  );
};

export default BookTreeWidget;
