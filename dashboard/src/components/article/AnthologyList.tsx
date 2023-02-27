import { useState, useEffect } from "react";
import { List } from "antd";

import { get } from "../../request";
import type { IAnthologyListResponse } from "../api/Article";
import AnthologyCard from "./AnthologyCard";
import type { IAnthologyData } from "./AnthologyCard";

interface IWidgetAnthologyList {
  view: string;
  id?: string;
}
const Widget = (prop: IWidgetAnthologyList) => {
  const [tableData, setTableData] = useState<IAnthologyData[]>([]);

  useEffect(() => {
    console.log("useEffect", prop);
    if (typeof prop.id === "undefined") {
      fetchData(prop.view);
    } else {
      fetchData(prop.view, prop.id);
    }
  }, [prop]);

  function fetchData(view: string, id?: string) {
    let url = `/v2/anthology?view=${view}` + (id ? `&studio=${id}` : "");
    console.log("get-url", url);
    get<IAnthologyListResponse>(url).then(function (response) {
      console.log("ajex", response);
      let newTree: IAnthologyData[] = response.data.rows.map((item) => {
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
    });
  }

  return (
    <List
      itemLayout="vertical"
      size="large"
      dataSource={tableData}
      renderItem={(item) => (
        <List.Item>
          <AnthologyCard data={item} />
        </List.Item>
      )}
    />
  );
};

export default Widget;
