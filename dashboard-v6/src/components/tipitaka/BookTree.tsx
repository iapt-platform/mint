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

import type { IPaliBookListResponse } from "../../api/Corpus";

import type { ITocTree } from "./BookTreeList";
import { PaliToEn } from "../../utils";

import type { EventDataNode } from "antd/lib/tree";
import PaliText from "../general/PaliText";
import TocStyleSelect from "./TocStyleSelect";
import type { IFtsData } from "../../api/fts";

const { Text } = Typography;

interface IWidgetBookTree {
  root?: string;
  path?: string[];
  multiSelect?: boolean;
  multiSelectable?: boolean;
  books?: IFtsData[];
  /** 自定义节点计数（参数是节点的 tag）；给了就不用 books 数书本数，计数为 0 的节点变淡 */
  count?: (tags: string[]) => number;
  /** 受控的选中节点（节点 key 是 tag 用逗号连起来） */
  selectedKeys?: Key[];
  onChange?: (selectedKeys: Key[], path?: string[]) => void;
  onSelect?: (key?: Key) => void;
  onRootChange?: (value: string) => void;
}

const BookTreeWidget = ({
  root,
  multiSelect = false,
  multiSelectable = true,
  books,
  count: countOf,
  selectedKeys: controlledKeys,
  onChange,
  onSelect,
  onRootChange,
}: IWidgetBookTree) => {
  const intl = useIntl();
  const [treeData, setTreeData] = useState<ITocTree[]>([]);
  const [innerKeys, setSelectedKeys] = useState<Key[]>([]);
  const selectedKeys = controlledKeys ?? innerKeys;
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
        `/api/v2/pali-book-category/${value}`,
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

  /** 节点 key → 它的全部祖先 key */
  const ancestorsOf = (key: Key): Key[] => {
    const walk = (nodes: ITocTree[], path: Key[]): Key[] | undefined => {
      for (const node of nodes) {
        if (node.key === key) return path;
        const found = walk(node.children, [...path, node.key]);
        if (found) return found;
      }
      return undefined;
    };
    return walk(treeData, []) ?? [];
  };

  /**
   * 多选时，新选中的节点与已选的祖先 / 后代互斥：选了子目录，父目录取消；选了父目录，子目录取消
   * （父目录已经包含子目录，两个都选没有意义，计数也会重复）。
   */
  const exclusive = (keys: Key[], picked: Key): Key[] => {
    const up = ancestorsOf(picked);
    return keys.filter(
      (k) =>
        k === picked || (!up.includes(k) && !ancestorsOf(k).includes(picked)),
    );
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
    },
  ) => {
    const node: ITocTree = info.node;
    if (isMultiSelect && info.selected) {
      selectedKeys = exclusive(selectedKeys, node.key);
    }
    setSelectedKeys(selectedKeys);

    if (typeof onChange !== "undefined") {
      onChange(selectedKeys, node.path);
    }
    if (typeof onSelect !== "undefined") {
      onSelect(selectedKeys.length > 0 ? selectedKeys[0] : undefined);
    }
  };

  const titleRender = (node: ITocTree) => {
    if (countOf) {
      const n = countOf(node.tag);
      return (
        <Space style={{ opacity: n ? 1 : 0.45 }}>
          <PaliText text={node.title} />
          {n ? (
            <Text
              type="secondary"
              style={{ fontVariantNumeric: "tabular-nums" }}
            >
              {n.toLocaleString()}
            </Text>
          ) : null}
        </Space>
      );
    }
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
                // 切回单选时只留最后选的那一个
                if (!checked && selectedKeys.length > 1) {
                  const last = selectedKeys.slice(-1);
                  setSelectedKeys(last);
                  onChange?.(last);
                  onSelect?.(last[0]);
                }
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
          // 自定义图标时 antd 不会替它旋转（showLine 下尤其如此），按展开状态自己转；叶子不显示
          switcherIcon={({ expanded, isLeaf }) =>
            isLeaf ? null : <DownOutlined rotate={expanded ? 0 : -90} />
          }
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
