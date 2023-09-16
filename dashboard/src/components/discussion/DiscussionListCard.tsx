import { useEffect, useRef, useState } from "react";
import { Space, Typography } from "antd";
import { CommentOutlined } from "@ant-design/icons";

import { get } from "../../request";
import { ICommentListResponse } from "../api/Comment";
import { IComment } from "./DiscussionItem";
import { IAnswerCount } from "./DiscussionDrawer";
import { ActionType, ProList } from "@ant-design/pro-components";
import { renderBadge } from "../channel/ChannelTable";
import DiscussionCreate from "./DiscussionCreate";
import User from "../auth/User";

const { Link } = Typography;

export type TResType = "article" | "channel" | "chapter" | "sentence" | "wbw";

interface IWidget {
  resId?: string;
  resType?: TResType;
  topicId?: string;
  changedAnswerCount?: IAnswerCount;
  onSelect?: Function;
  onItemCountChange?: Function;
  onReply?: Function;
}
const DiscussionListCardWidget = ({
  resId,
  resType,
  topicId,
  onSelect,
  changedAnswerCount,
  onItemCountChange,
  onReply,
}: IWidget) => {
  const ref = useRef<ActionType>();
  const [activeKey, setActiveKey] = useState<React.Key | undefined>("active");
  const [activeNumber, setActiveNumber] = useState<number>(0);
  const [closeNumber, setCloseNumber] = useState<number>(0);
  const [count, setCount] = useState<number>(0);

  useEffect(() => {
    ref.current?.reload();
  }, [resId, resType]);

  useEffect(() => {
    console.log("changedAnswerCount", changedAnswerCount);
    ref.current?.reload();
  }, [changedAnswerCount]);

  if (typeof resId === "undefined" && typeof topicId === "undefined") {
    return (
      <Typography.Paragraph>
        该资源尚未创建，不能发表讨论。
      </Typography.Paragraph>
    );
  }
  return (
    <>
      <ProList<IComment>
        rowKey="id"
        actionRef={ref}
        metas={{
          avatar: {
            render(dom, entity, index, action, schema) {
              return (
                <>
                  <User {...entity.user} showName={false} />
                </>
              );
            },
          },
          title: {
            render(dom, entity, index, action, schema) {
              return (
                <>
                  <Link
                    strong
                    onClick={(event) => {
                      if (typeof onSelect !== "undefined") {
                        onSelect(event, entity);
                      }
                    }}
                  >
                    {entity.title}
                  </Link>
                </>
              );
            },
          },
          description: {
            dataIndex: "content",
            search: false,
          },
          actions: {
            render: (text, row, index, action) => [
              row.childrenCount ? (
                <Space key={index}>
                  <CommentOutlined />
                  {row.childrenCount}
                </Space>
              ) : (
                <></>
              ),
            ],
          },
        }}
        request={async (params = {}, sorter, filter) => {
          let url: string = "/v2/discussion?";
          if (typeof topicId !== "undefined") {
            url += `view=question-by-topic&id=${topicId}`;
          } else if (typeof resId !== "undefined") {
            url += `view=question&id=${resId}`;
          } else {
            return {
              total: 0,
              succcess: false,
            };
          }
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";
          url += activeKey ? "&status=" + activeKey : "";
          console.log("url", url);
          const res = await get<ICommentListResponse>(url);
          setCount(res.data.active);
          const items: IComment[] = res.data.rows.map((item, id) => {
            return {
              id: item.id,
              resId: item.res_id,
              resType: item.res_type,
              user: item.editor,
              title: item.title,
              parent: item.parent,
              content: item.content,
              status: item.status,
              childrenCount: item.children_count,
              createdAt: item.created_at,
              updatedAt: item.updated_at,
            };
          });
          setActiveNumber(res.data.active);
          setCloseNumber(res.data.close);
          return {
            total: res.data.count,
            succcess: true,
            data: items,
          };
        }}
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
          pageSize: 20,
        }}
        search={false}
        options={{
          search: false,
        }}
        toolbar={{
          menu: {
            activeKey,
            items: [
              {
                key: "active",
                label: (
                  <span>
                    active
                    {renderBadge(activeNumber, activeKey === "active")}
                  </span>
                ),
              },
              {
                key: "close",
                label: (
                  <span>
                    close
                    {renderBadge(closeNumber, activeKey === "close")}
                  </span>
                ),
              },
            ],
            onChange(key) {
              console.log("show course", key);
              setActiveKey(key);
              ref.current?.reload();
            },
          },
        }}
      />

      {resId && resType ? (
        <DiscussionCreate
          contentType="markdown"
          resId={resId}
          resType={resType}
          onCreated={(e: IComment) => {
            if (typeof onItemCountChange !== "undefined") {
              onItemCountChange(count + 1);
            }
            ref.current?.reload();
          }}
        />
      ) : undefined}
    </>
  );
};

export default DiscussionListCardWidget;