import { useState, useEffect } from "react";
import { List } from "antd";

import { get } from "../../request";
import type { IAnthologyListResponse } from "../api/Article";
import AnthologyCard from "./AnthologyCard";
import type { IAnthologyData } from "./AnthologyCard";

interface IWidget {
  studioName?: string;
  searchKey?: string;
}
const AnthologyListWidget = ({ studioName, searchKey }: IWidget) => {
  const [tableData, setTableData] = useState<IAnthologyData[]>([]);
  const [total, setTotal] = useState<number>();
  const [currPage, setCurrPage] = useState<number>(1);
  const pageSize = 20;

  useEffect(() => {
    const offset = (currPage - 1) * pageSize;
    let url = `/v2/anthology?view=public&offset=${offset}&limit=${pageSize}`;
    if (typeof studioName !== "undefined") {
      url += `&studio=${studioName}`;
    }
    if (typeof searchKey === "string" && searchKey.length > 0) {
      url += `&search=${searchKey}`;
    }

    console.log("get-url", url);
    get<IAnthologyListResponse>(url).then(function (json) {
      if (json.ok) {
        let newTree: IAnthologyData[] = json.data.rows.map((item) => {
          return {
            id: item.uid,
            title: item.title,
            subTitle: item.subtitle,
            summary: item.summary,
            articles: item.article_list.map((al) => {
              return {
                key: al.article,
                title: al.title,
                level: parseInt(al.level),
              };
            }),
            studio: item.studio,
            created_at: item.created_at,
            updated_at: item.updated_at,
          };
        });
        setTableData(newTree);
        setTotal(json.data.count);
      } else {
        setTableData([]);
        setTotal(0);
      }
    });
  }, [currPage, searchKey, studioName]);

  return (
    <List
      itemLayout="vertical"
      size="large"
      dataSource={tableData}
      pagination={{
        onChange: (page) => {
          console.log(page);
          setCurrPage(page);
        },
        showQuickJumper: true,
        showSizeChanger: false,
        pageSize: pageSize,
        total: total,
        position: "both",
        showTotal: (total) => {
          return `结果: ${total}`;
        },
      }}
      renderItem={(item) => (
        <List.Item>
          <AnthologyCard data={item} />
        </List.Item>
      )}
    />
  );
};

export default AnthologyListWidget;
