import { Badge, List } from "antd";
import { useEffect, useState } from "react";

import { get } from "../../request";

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
  const [ftsData, setFtsData] = useState<IFtsItem[]>();
  const [total, setTotal] = useState<number>();

  const focusBooks = bookId?.split(",");
  console.log("focusBooks", focusBooks);
  useEffect(() => {
    let words;
    let api = "";
    switch (engin) {
      case "wbw":
        api = "search-pali-wbw-books";
        words = keyWords?.join();
        break;
      case "tulip":
        api = "search-book-list";
        words = keyWord;
        break;
      default:
        break;
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
        const result: IFtsItem[] = json.data.rows.map((item) => {
          return item;
        });
        setFtsData([
          {
            book: 0,
            paragraph: 0,
            title: "全部",
            pcdBookId: 0,
            count: totalResult,
          },
          ...result,
        ]);
        setTotal(json.data.count);
      }
    });
  }, [keyWord, match, tags]);
  return (
    <List
      header={`总计：` + total}
      itemLayout="vertical"
      size="small"
      dataSource={ftsData}
      renderItem={(item, id) => (
        <List.Item>
          <div
            style={{
              padding: 4,
              borderRadius: 4,
              display: "flex",
              justifyContent: "space-between",
              cursor: "pointer",
              backgroundColor: focusBooks?.includes(item.pcdBookId.toString())
                ? "lightblue"
                : "unset",
            }}
            onClick={() => {
              if (typeof onSelect !== "undefined") {
                onSelect(item.pcdBookId);
              }
            }}
          >
            <span>
              {id + 1}.{item.title ? item.title : item.paliTitle}
            </span>
            <Badge color="geekblue" count={item.count} />
          </div>
        </List.Item>
      )}
    />
  );
};

export default FtsBookListWidget;
