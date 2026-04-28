import { ProList } from "@ant-design/pro-components";
import { Space, Typography } from "antd";

import { get } from "../../request";
import User from "../auth/User";
import TimeShow from "../general/TimeShow";

import { MergeIcon2 } from "../../assets/icon";
import type {
  ISentHistory,
  ISentHistoryListResponse,
} from "../../api/sentence-history";

const { Paragraph } = Typography;

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

        let url = `/api/v2/sent_history?view=sentence&id=${sentId}`;
        const offset =
          ((params.current ? params.current : 1) - 1) *
          (params.pageSize ? params.pageSize : 20);
        url += `&limit=${params.pageSize}&offset=${offset}`;
        if (typeof params.keyword !== "undefined") {
          url += "&search=" + (params.keyword ? params.keyword : "");
        }
        console.debug("sentence history list", url);
        const res = await get<ISentHistoryListResponse>(url);
        if (res.ok) {
          console.debug("sentence history list", res.data);
          const items: ISentHistory[] = res.data.rows.map((item) => {
            return {
              content: item.content,
              editor: item.editor,
              fork_from: item.fork_from,
              pr_from: item.pr_from,
              accepter: item.accepter,
              createdAt: item.created_at,
            };
          });
          console.debug(items);
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
          render: (_text, row) => {
            return (
              <Paragraph style={{ margin: 0 }} copyable={{ text: row.content }}>
                {row.content}
              </Paragraph>
            );
          },
        },
        avatar: {
          dataIndex: "image",
          editable: false,
          render: (_text, row) => {
            return <User {...row.editor} showName={false} />;
          },
        },
        description: {
          render: (_text, row) => {
            return (
              <Space style={{ fontSize: "80%" }}>
                <User {...row.editor} showAvatar={false} />
                <>{"edited"}</>

                {row.accepter ? (
                  <>
                    <User {...row.accepter} showAvatar={false} /> {"accept"}
                  </>
                ) : (
                  <></>
                )}

                {row.fork_from ? (
                  <>
                    <MergeIcon2 />
                    {row.fork_from.name}
                  </>
                ) : (
                  <></>
                )}
                <TimeShow
                  type="secondary"
                  createdAt={row.createdAt}
                  showLabel={false}
                />
              </Space>
            );
          },
        },
        actions: {
          render: () => [<></>],
        },
      }}
    />
  );
};

export default SentHistoryWidget;
