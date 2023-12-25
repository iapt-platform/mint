import { useEffect, useRef, useState } from "react";
import { Button, Space, Typography } from "antd";
import { CommentOutlined } from "@ant-design/icons";

import { get } from "../../request";
import { ICommentListResponse } from "../api/Comment";
import { IComment } from "./DiscussionItem";
import { IAnswerCount } from "./DiscussionDrawer";
import { ActionType, ProList } from "@ant-design/pro-components";
import { renderBadge } from "../channel/ChannelTable";
import DiscussionCreate from "./DiscussionCreate";
import User from "../auth/User";
import { IArticleListResponse } from "../api/Article";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { CommentOutlinedIcon, TemplateOutlinedIcon } from "../../assets/icon";
import { ISentenceResponse } from "../api/Corpus";

export type TResType =
  | "article"
  | "channel"
  | "chapter"
  | "sentence"
  | "wbw"
  | "term";

interface IWidget {
  resId?: string;
  resType?: TResType;
  topicId?: string;
  changedAnswerCount?: IAnswerCount;
  onSelect?: Function;
  onItemCountChange?: Function;
  onReply?: Function;
  onReady?: Function;
}
const DiscussionListCardWidget = ({
  resId,
  resType,
  topicId,
  onSelect,
  changedAnswerCount,
  onItemCountChange,
  onReply,
  onReady,
}: IWidget) => {
  const ref = useRef<ActionType>();
  const [activeKey, setActiveKey] = useState<React.Key | undefined>("active");
  const [activeNumber, setActiveNumber] = useState<number>(0);
  const [closeNumber, setCloseNumber] = useState<number>(0);
  const [count, setCount] = useState<number>(0);
  const user = useAppSelector(_currentUser);

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
                <Button
                  key={index}
                  size="small"
                  type="link"
                  icon={entity.newTpl ? <TemplateOutlinedIcon /> : undefined}
                  onClick={(event) => {
                    if (typeof onSelect !== "undefined") {
                      onSelect(event, entity);
                    }
                  }}
                >
                  {entity.title}
                </Button>
              );
            },
          },
          description: {
            dataIndex: "content",
            search: false,
            render(dom, entity, index, action, schema) {
              return (
                <span key={index}>
                  {entity.summary ? entity.summary : entity.content}
                </span>
              );
            },
          },
          actions: {
            render: (text, row, index, action) => [
              row.childrenCount ? (
                <Space key={index}>
                  <CommentOutlinedIcon key={"icon"} />
                  <span key={"count"}>{row.childrenCount}</span>
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
              tplId: item.tpl_id,
              content: item.content,
              summary: item.summary,
              status: item.status,
              childrenCount: item.children_count,
              createdAt: item.created_at,
              updatedAt: item.updated_at,
            };
          });

          let topicTpl: IComment[] = [];
          if (activeKey !== "close") {
            //获取channel模版
            let studioName: string | undefined;
            switch (resType) {
              case "sentence":
                const url = `/v2/sentence/${resId}`;
                console.log("url", url);
                const sentInfo = await get<ISentenceResponse>(url);
                studioName = sentInfo.data.studio.realName;
                break;
            }
            const urlTpl = `/v2/article?view=template&studio_name=${studioName}&subtitle=_template_discussion_topic_&content=true`;
            const resTpl = await get<IArticleListResponse>(urlTpl);
            if (resTpl.ok) {
              console.log("resTpl.data.rows", resTpl.data.rows);
              topicTpl = resTpl.data.rows
                .filter(
                  (value) =>
                    items.findIndex((old) => old.tplId === value.uid) === -1
                )
                .map((item, index) => {
                  return {
                    tplId: item.uid,
                    resId: resId,
                    resType: resType,
                    user: item.editor
                      ? item.editor
                      : { id: "", userName: "", nickName: "" },
                    title: item.title,
                    parent: null,
                    content: item.content,
                    html: item.html,
                    summary: item.summary ? item.summary : item._summary,
                    status: "active",
                    childrenCount: 0,
                    newTpl: true,
                    createdAt: item.created_at,
                    updatedAt: item.updated_at,
                  };
                });
            }
          }

          setActiveNumber(res.data.active);
          setCloseNumber(res.data.close);
          if (typeof onReady !== "undefined") {
            onReady();
          }
          return {
            total: res.data.count,
            succcess: true,
            data: [...topicTpl, ...items],
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
