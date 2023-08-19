import { ProList } from "@ant-design/pro-components";
import { Typography } from "antd";

import { get } from "../../request";
import { IUser } from "../auth/UserName";
import TimeShow from "../general/TimeShow";

const { Text } = Typography;

interface ISentHistoryData {
  content: string;
  editor: IUser;
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
      headerTitle={"time line"}
      showActions="hover"
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
            return <TimeShow type="secondary" createdAt={row.createdAt} />;
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
