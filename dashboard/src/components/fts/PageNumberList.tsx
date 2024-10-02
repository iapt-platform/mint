import { Badge, Card, Select, Space, Tree } from "antd";

import { useEffect, useState } from "react";
import { get } from "../../request";
import { DataNode } from "antd/es/tree";
import { useIntl } from "react-intl";
import { Key } from "antd/es/table/interface";
import { bookName } from "./book_name";

export interface IPageNumber {
  type: string;
  volume: number;
  page: number;
  book: number;
  paragraph: number;
  pcd_book_id: number;
}
export interface IPageNumberListResponse {
  ok: boolean;
  message: string;
  data: IPageNumber[];
}
interface IType {
  key: string;
  value: number;
}
interface IWidget {
  keyWord?: string;
  onSelect?: Function;
}
const CaseListWidget = ({ keyWord, onSelect }: IWidget) => {
  const intl = useIntl();
  const [pageData, setPageData] = useState<IPageNumber[]>();
  const [types, setTypes] = useState<IType[]>();
  const [treeData, setTreeData] = useState<DataNode[]>();

  const booksTitle = (type: string): [string[], Map<string, number[]>] => {
    let bookNameMap = new Map<string, number[]>();
    bookName.forEach((value) => {
      let name: string;
      switch (type) {
        case "M":
          name = value.m_title;
          break;
        case "P":
          name = value.p_title;
          break;
        case "V":
          name = value.v_title;
          break;
        default:
          name = value.term;
          break;
      }

      if (bookNameMap.has(name)) {
        const id = bookNameMap.get(name);
        if (id) {
          id.push(value.id);
          bookNameMap.set(name, id);
        } else {
          bookNameMap.set(name, [value.id]);
        }
      } else {
        bookNameMap.set(name, [value.id]);
      }
    });
    let bookNameList: string[] = [];
    bookNameMap.forEach((value, key) => {
      bookNameList.push(key);
    });
    return [bookNameList, bookNameMap];
  };

  useEffect(() => {
    if (typeof keyWord === "undefined") {
      return;
    }
    get<IPageNumberListResponse>(`/v2/search-page-number/${keyWord}`).then(
      (json) => {
        console.log("case", json);
        if (json.ok) {
          setPageData(json.data);
          const typeCount = new Map<string, number>();
          json.data.forEach((value) => {
            const old = typeCount.get(value.type);
            if (typeof old === "undefined") {
              typeCount.set(value.type, 1);
            } else {
              typeCount.set(value.type, old + 1);
            }
          });
          let mType: IType[] = [];
          typeCount.forEach((value, key) => {
            mType.push({ key: key, value: value });
          });
          setTypes(mType);

          const tData = mType.map((item, id1) => {
            let volumes: number[] = [];
            json.data
              .filter((value) => value.type === item.key)
              .forEach((value) => {
                if (!volumes.includes(value.volume)) {
                  volumes.push(value.volume);
                }
              });
            const keys = volumes
              .map((item1) => {
                if (item.key === "para") {
                  return `${item.key}${keyWord}`;
                } else {
                  return `${item.key}${item1}.` + pageNumberToStr(keyWord);
                }
              })
              .join();
            const [bookNameList, bookNameMap] = booksTitle(item.key);
            return {
              title: (
                <Space>
                  {intl.formatMessage({
                    id: `labels.page.number.type.${item.key}`,
                  })}
                  <Badge
                    size="small"
                    status="default"
                    color={"lime"}
                    count={item.value}
                    overflowCount={999}
                  />
                </Space>
              ),
              key: `${id1}-${keys}-0`,
              children: bookNameList
                .map((bookItem, id2) => {
                  const bookId = bookNameMap.get(bookItem);
                  const bookChildren = json.data.filter(
                    (value) =>
                      value.type === item.key &&
                      bookId?.includes(value.pcd_book_id)
                  );
                  let pcdBookId: number[] = [];
                  bookChildren.forEach((value) => {
                    if (!pcdBookId.includes(value.pcd_book_id)) {
                      pcdBookId.push(value.pcd_book_id);
                    }
                  });
                  return {
                    title: bookItem + `[${bookChildren.length}]`,
                    key: `${id1}_${id2}-${keys}-${pcdBookId.join()}`,
                    children: bookChildren.map((item1, id3) => {
                      return {
                        title: `${item1.type}-${item1.volume}-${item1.page}-${item1.book}-${item1.paragraph}`,
                        key: `${id1}-${id2}-${id3}-${item1.type}-${item1.volume}-${item1.page}-${item1.book}-${item1.paragraph}`,
                        disabled: true,
                      };
                    }),
                  };
                })
                .filter((value) => !value.title.includes("[0]"))
                .sort((a, b) => {
                  const nameA = a.title?.toLowerCase(); // ignore upper and lowercase
                  const nameB = b.title?.toLowerCase(); // ignore upper and lowercase
                  if (!nameA || !nameB) {
                    return 0;
                  }
                  if (nameA < nameB) {
                    return -1;
                  }
                  if (nameA > nameB) {
                    return 1;
                  }
                  // names must be equal
                  return 0;
                }),
            };
          });
          setTreeData(tData);
        }
      }
    );
  }, [intl, keyWord]);

  const pageNumberToStr = (page: number | string): string => {
    const strPage = page.toString();
    const zero = 4 - strPage.length;
    return Array(zero).fill("0").join("") + strPage;
  };
  return (
    <div style={{ padding: 4 }}>
      <Card
        size="small"
        title={
          <Select
            value={"all"}
            bordered={false}
            onChange={(value: string) => {}}
            options={types?.map((item, id) => {
              return {
                label: (
                  <Space>
                    {item.key}
                    <Badge
                      count={item.value}
                      color={"lime"}
                      status="default"
                      size="small"
                    />
                  </Space>
                ),
                value: item.key,
              };
            })}
          />
        }
      >
        <Tree
          onSelect={(selectedKeys: Key[]) => {
            if (typeof onSelect !== "undefined") {
              onSelect(selectedKeys);
            }
          }}
          treeData={treeData}
        />
      </Card>
    </div>
  );
};

export default CaseListWidget;
