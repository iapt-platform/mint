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
  tags?: string[];
  bookId?: number;
  book?: number;
  para?: number;
  match?: string | null;
  keyWord2?: string;
  view?: string;
  onSelect?: Function;
}

const Widget = ({
  keyWord,
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

  useEffect(() => {
    let url = `/v2/search-book-list?view=${view}&key=${keyWord}`;
    if (typeof tags !== "undefined") {
      url += `&tags=${tags}`;
    }
    if (match) {
      url += `&match=${match}`;
    }
    console.log("url", url);
    get<IFtsResponse>(url).then((json) => {
      if (json.ok) {
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
              display: "flex",
              justifyContent: "space-between",
              cursor: "pointer",
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

export default Widget;
