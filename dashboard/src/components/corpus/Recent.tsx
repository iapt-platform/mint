import { Button, List } from "antd";
import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { IView, IViewListResponse } from "../../pages/studio/recent/list";
import { get } from "../../request";

const RecentWidget = () => {
  const [listData, setListData] = useState<IView[]>([]);
  useEffect(() => {
    let url = `/v2/view?view=user&limit=10`;
    get<IViewListResponse>(url).then((json) => {
      if (json.ok) {
        const items: IView[] = json.data.rows.map((item, id) => {
          return {
            sn: id + 1,
            id: item.id,
            title: item.title,
            subtitle: item.org_title,
            type: item.target_type,
            meta: JSON.parse(item.meta),
            updatedAt: item.updated_at,
          };
        });
        setListData(items);
      }
    });
  }, []);
  return (
    <div style={{ padding: 6 }}>
      <List
        itemLayout="vertical"
        header="最近打开"
        size="small"
        dataSource={listData}
        renderItem={(item) => {
          let url = `/article/${item.type}/`;
          switch (item.type) {
            case "chapter":
              url += item.meta.book + "-" + item.meta.para;
              break;

            default:
              break;
          }
          return (
            <List.Item>
              <Link to={url} target="_blank">
                {item.title ? item.title : item.subtitle}
              </Link>
            </List.Item>
          );
        }}
      />
      <Button type="link">更多</Button>
    </div>
  );
};

export default RecentWidget;
