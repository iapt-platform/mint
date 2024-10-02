import { Badge, Card, Space, Tree, Typography } from "antd";
import { useEffect, useState } from "react";

import { get } from "../../request";
import { Key } from "antd/es/table/interface";
import { DataNode } from "antd/es/tree";
const { Text } = Typography;
interface IFtsData {
  book: number;
  paragraph: number;
  title?: string;
  paliTitle: string;
  pcdBookId: number;
  count: number;
}
interface IFtsResponse {
  ok: boolean;
  string: string;
  data: {
    rows: IFtsData[];
    count: number;
  };
}
interface IFtsItem {
  book: number;
  paragraph: number;
  title?: string;
  paliTitle?: string;
  pcdBookId: number;
  count: number;
}
interface IWidget {
  keyWord?: string;
  keyWords?: string[];
  engin?: "wbw" | "tulip";
  tags?: string[];
  bookId?: string | null;
  book?: number;
  para?: number;
  match?: string | null;
  keyWord2?: string;
  view?: string;
  onSelect?: Function;
}

const FtsBookListWidget = ({
  keyWord,
  keyWords,
  engin = "wbw",
  tags,
  bookId,
  book,
  para,
  keyWord2,
  match,
  view = "pali",
  onSelect,
}: IWidget) => {
  const [treeData, setTreeData] = useState<DataNode[]>();
  const [total, setTotal] = useState<number>();
  const [checkedKeys, setCheckedKeys] = useState<
    | {
        checked: Key[];
        halfChecked: Key[];
      }
    | Key[]
  >([0]);
  const [selectedKeys, setSelectedKeys] = useState<React.Key[]>([]);
  const [expandedKeys, setExpandedKeys] = useState<React.Key[]>(["all"]);

  const focusBooks = bookId?.split(",");
  console.log("focusBooks", focusBooks);

  useEffect(() => {
    const currBooks = bookId?.split(",").map((item) => parseInt(item));
    if (currBooks) {
      console.log("currBooks", currBooks);
      setCheckedKeys(currBooks);
      setSelectedKeys(currBooks);
      setExpandedKeys(["all"]);
    }
  }, [bookId, treeData]);

  useEffect(() => {
    let words;
    let api = "";
    if (keyWord?.trim().includes(" ")) {
      api = "search-book-list";
      words = keyWord;
    } else {
      api = "search-pali-wbw-books";
      words = keyWords?.join();
    }

    let url = `/v2/${api}?view=${view}&key=${words}`;
    if (typeof tags !== "undefined") {
      url += `&tags=${tags}`;
    }
    if (match) {
      url += `&match=${match}`;
    }
    console.log("url", url);
    get<IFtsResponse>(url).then((json) => {
      if (json.ok) {
        console.log("data", json.data.rows);
        let totalResult = 0;
        for (const iterator of json.data.rows) {
          totalResult += iterator.count;
        }

        setTreeData([
          {
            key: "all",
            title: "all " + totalResult + "个结果",
            children: json.data.rows.map((item, id) => {
              const title = item.title ? item.title : item.paliTitle;
              return {
                key: item.pcdBookId,
                title: (
                  <Space>
                    <Text
                      style={{ whiteSpace: "nowrap", width: 200 }}
                      ellipsis={{ tooltip: { title } }}
                    >
                      {id + 1}.{title}
                    </Text>
                    <Badge size="small" color="geekblue" count={item.count} />
                  </Space>
                ),
              };
            }),
          },
        ]);

        setTotal(json.data.count);
      }
    });
  }, [keyWord, keyWords, match, tags, view]);

  const onExpand = (expandedKeysValue: React.Key[]) => {
    console.log("onExpand", expandedKeysValue);
    // if not set autoExpandParent to false, if children expanded, parent can not collapse.
    // or, you can remove all expanded children keys.
    setExpandedKeys(expandedKeysValue);
  };

  const onCheck = (
    checked:
      | {
          checked: Key[];
          halfChecked: Key[];
        }
      | Key[]
  ) => {
    console.log("onCheck", checked);
    setCheckedKeys(checked);
    if (typeof onSelect !== "undefined") {
      onSelect(checked.toString());
    }
  };

  return (
    <div style={{ padding: 4 }}>
      <Card
        size="small"
        title={
          <Space>
            {"总计"}
            <Badge
              size="small"
              count={total}
              overflowCount={999}
              color="lime"
            />
            {"本书"}
          </Space>
        }
      >
        <Tree
          checkable
          defaultExpandAll
          onExpand={onExpand}
          onCheck={onCheck}
          checkedKeys={checkedKeys}
          expandedKeys={expandedKeys}
          onSelect={(selectedKeysValue: React.Key[], info: any) => {
            console.log("onSelect", selectedKeysValue);
            setSelectedKeys(selectedKeysValue);
            setCheckedKeys(selectedKeysValue);
            if (typeof onSelect !== "undefined") {
              if (selectedKeysValue.length > 0) {
                if (selectedKeysValue[0] === "all") {
                  onSelect(0);
                } else {
                  onSelect(selectedKeysValue[0]);
                }
              }
            }
          }}
          selectedKeys={selectedKeys}
          treeData={treeData}
        />
      </Card>
    </div>
  );
};

export default FtsBookListWidget;
