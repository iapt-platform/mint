import { useEffect, useRef, useState } from "react";

import { Collapse, Typography } from "antd";

import { get, put } from "../../request";
import {
  ICommentListResponse,
  ICommentRequest,
  ICommentResponse,
} from "../api/Comment";

import DiscussionItem, { IComment } from "./DiscussionItem";

import { IAnswerCount } from "./DiscussionDrawer";
import { ActionType, ProList } from "@ant-design/pro-components";
import { renderBadge } from "../channel/ChannelTable";
import DiscussionCreate from "./DiscussionCreate";
const { Panel } = Collapse;

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
        itemLayout="vertical"
        rowKey="id"
        actionRef={ref}
        metas={{
          avatar: {
            render(dom, entity, index, action, schema) {
              return <></>;
            },
          },
          title: {
            render(dom, entity, index, action, schema) {
              return <></>;
            },
          },
          content: {
            render: (text, row, index, action) => {
              return (
                <DiscussionItem
                  data={row}
                  onSelect={(
                    e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
                    data: IComment
                  ) => {
                    if (typeof onSelect !== "undefined") {
                      onSelect(e, data);
                    }
                  }}
                  onDelete={() => {
                    ref.current?.reload();
                  }}
                  onReply={() => {
                    if (typeof onReply !== "undefined") {
                      onReply(row);
                    }
                  }}
                  onClose={(value: boolean) => {
                    console.log("comment", row);
                    put<ICommentRequest, ICommentResponse>(
                      `/v2/discussion/${row.id}`,
                      {
                        title: row.title,
                        content: row.content,
                        status: value ? "close" : "active",
                      }
                    ).then((json) => {
                      console.log(json);
                      if (json.ok) {
                        ref.current?.reload();
                      }
                    });
                  }}
                />
              );
            },
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
