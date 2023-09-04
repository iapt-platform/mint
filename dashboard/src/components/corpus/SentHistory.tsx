import { ProList } from "@ant-design/pro-components";
import { Space } from "antd";

import { get } from "../../request";
import User from "../auth/User";
import { IUser } from "../auth/UserName";
import TimeShow from "../general/TimeShow";

interface ISentHistoryData {
  id: string;
  sent_uid: string;
  content: string;
  editor: IUser;
  landmark: string;
  created_at: string;
}

interface ISentHistoryListResponse {
  ok: boolean;
  message: string;
  data: { rows: ISentHistoryData[]; count: number };
}

interface ISentHistory {
  content: string;
  editor: IUser;
  createdAt: string;
}
interface IWidget {
  sentId?: string;
}
const SentHistoryWidget = ({ sentId }: IWidget) => {
  return (
    <ProList<ISentHistory>
      rowKey="id"
      request={async (params = {}, sorter, filter) => {
        if (typeof sentId === "undefined") {
          return {
            total: 0,
            succcess: false,
            data: [],
          };
        }
        console.log(params, sorter, filter);

        let url = `/v2/sent_history?view=sentence&id=${sentId}`;
        const offset =
          ((params.current ? params.current : 1) - 1) *
          (params.pageSize ? params.pageSize : 20);
        url += `&limit=${params.pageSize}&offset=${offset}`;
        if (typeof params.keyword !== "undefined") {
          url += "&search=" + (params.keyword ? params.keyword : "");
        }
        const res = await get<ISentHistoryListResponse>(url);
        if (res.ok) {
          console.log(res.data);

          const items: ISentHistory[] = res.data.rows.map((item, id) => {
            return {
              content: item.content,
              editor: item.editor,
              createdAt: item.created_at,
            };
          });
          console.log(items);
          return {
            total: res.data.count,
            succcess: true,
            data: items,
          };
        } else {
          console.error(res.message);
          return {
            total: 0,
            succcess: false,
            data: [],
          };
        }
      }}
      pagination={{
        showQuickJumper: true,
        showSizeChanger: true,
      }}
      metas={{
        title: {
          dataIndex: "content",
        },
        avatar: {
          dataIndex: "image",
          editable: false,
        },
        description: {
          render: (text, row, index, action) => {
            return (
              <Space style={{ fontSize: "80%" }}>
                <User {...row.editor} />
                <TimeShow type="secondary" createdAt={row.createdAt} />
              </Space>
            );
          },
        },
        actions: {
          render: (text, row, index, action) => [<></>],
        },
      }}
    />
  );
};

export default SentHistoryWidget;
