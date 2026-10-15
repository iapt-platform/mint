import { useEffect, useRef, useState } from "react";
import { Button, Space, Tag, Typography } from "antd";
import { LinkOutlined } from "@ant-design/icons";

import { get } from "../../request";

import { type ActionType, ProList } from "@ant-design/pro-components";
import DiscussionCreate from "./DiscussionCreate";
import User from "../auth/User";
import type { IArticleListResponse } from "../../api/article";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { CommentOutlinedIcon, TemplateOutlinedIcon } from "../../assets/icon";

import TimeShow from "../general/TimeShow";
import {
  asDiscussionTarget,
  fetchTopicSummary,
  fetchTopics,
  NO_ABILITIES,
  THREAD_TYPES,
  type IAnswerCount,
  type IComment,
  type TDiscussionStatus,
  type TDiscussionType,
  type TResType,
} from "../../api/discussion";
import type { ISentenceResponse } from "../../api/sentence";
import StatusBadge from "../general/StatusBadge";
import { SeverityTag } from "./SeverityTag";

const { Paragraph, Text } = Typography;

interface IWidget {
  resId?: string;
  resType?: TResType;
  changedAnswerCount?: IAnswerCount;
  /** 只列这种类型的话题；不传则列出全部类型 */
  type?: TDiscussionType;
  pageSize?: number;
  onSelect?: Function;
  onItemCountChange?: Function;
  onReady?: Function;
}
const DiscussionListCardWidget = ({
  resId,
  resType,
  onSelect,
  changedAnswerCount,
  type,
  pageSize = 10,
  onItemCountChange,
  onReady,
}: IWidget) => {
  const ref = useRef<ActionType | null>(null);
  const [activeKey, setActiveKey] = useState<TDiscussionStatus>("active");
  const [activeNumber, setActiveNumber] = useState<number>(0);
  const [closeNumber, setCloseNumber] = useState<number>(0);
  const [creatableTypes, setCreatableTypes] = useState<TDiscussionType[]>([]);

  const user = useAppSelector(_currentUser);
  const target = asDiscussionTarget(resType);

  useEffect(() => {
    ref.current?.reload();
  }, [resId, resType]);

  useEffect(() => {
    console.log("changedAnswerCount", changedAnswerCount);
    ref.current?.reload();
  }, [changedAnswerCount]);

  if (typeof resId === "undefined" || typeof target === "undefined") {
    return (
      <Typography.Paragraph>
        该资源尚未创建，不能发表讨论。
      </Typography.Paragraph>
    );
  }
  return (
    <>
      <ProList<IComment>
        rowKey={(row) => row.id ?? `tpl-${row.tplId}`}
        actionRef={ref}
        metas={{
          avatar: {
            render(_dom, entity, _index, _action, _schema) {
              return (
                <>
                  <User {...entity.user} showName={false} />
                </>
              );
            },
          },
          title: {
            render(_dom, entity, index, _action, _schema) {
              return (
                <>
                  <div>
                    {entity.resId !== resId ? <LinkOutlined /> : <></>}
                    {entity.deleted ? (
                      <Text type="secondary" italic>
                        （已删除）
                      </Text>
                    ) : (
                      <Button
                        key={index}
                        size="small"
                        type="link"
                        icon={
                          entity.newTpl ? <TemplateOutlinedIcon /> : undefined
                        }
                        onClick={(event) => {
                          if (typeof onSelect !== "undefined") {
                            onSelect(event, entity);
                          }
                        }}
                      >
                        {entity.title}
                      </Button>
                    )}
                    {entity.type !== "discussion" ? (
                      <Tag>{entity.type}</Tag>
                    ) : undefined}
                    <SeverityTag severity={entity.severity} />
                  </div>
                  <div>
                    <TimeShow
                      type="secondary"
                      showIcon={false}
                      createdAt={entity.createdAt}
                      updatedAt={entity.updatedAt}
                    />
                  </div>
                </>
              );
            },
          },
          description: {
            dataIndex: "content",
            search: false,
            render(_dom, entity, index, _action, _schema) {
              const content = entity.summary ?? entity.content ?? undefined;
              return (
                <div key={index}>
                  <Paragraph
                    type="secondary"
                    ellipsis={{
                      rows: 2,
                      expandable: true,
                    }}
                    title={content}
                  >
                    {content}
                  </Paragraph>
                </div>
              );
            },
          },
          actions: {
            render: (_text, row, index, _action) => [
              row.childrenCount ? (
                <Space
                  key={index}
                  style={{ cursor: "pointer" }}
                  onClick={(event) => onSelect?.(event, row)}
                >
                  <CommentOutlinedIcon key={"icon"} />
                  <span key={"count"}>{row.childrenCount}</span>
                </Space>
              ) : (
                <></>
              ),
            ],
          },
        }}
        request={async (params = {}, _sorter, _filter) => {
          const [res, summary] = await Promise.all([
            fetchTopics({
              target_type: target,
              target_id: resId,
              type,
              status: activeKey,
              page: params.current ?? 1,
              per_page: params.pageSize ?? pageSize,
            }),
            fetchTopicSummary({
              target_type: target,
              target_id: resId,
              type,
            }),
          ]);
          // summary 也会列出批注类型（note / commentary），讨论面板只新建评论线程
          setCreatableTypes(
            THREAD_TYPES.filter((value) =>
              summary.creatable_types?.includes(value)
            )
          );
          setActiveNumber(summary.status_counts?.active ?? 0);
          setCloseNumber(summary.status_counts?.close ?? 0);

          let topicTpl: IComment[] = [];
          if (
            activeKey !== "close" &&
            target === "sentence" &&
            user?.roles?.includes("basic") === false
          ) {
            //获取channel模版
            const url = `/api/v2/sentence/${resId}`;
            console.info("api request", url);
            const sentInfo = await get<ISentenceResponse>(url);
            const studioName = sentInfo.data.studio.realName;
            const urlTpl = `/api/v2/article?view=template&studio_name=${studioName}&subtitle=_template_discussion_topic_&content=true`;
            const resTpl = await get<IArticleListResponse>(urlTpl);
            if (resTpl.ok) {
              topicTpl = resTpl.data.rows
                .filter(
                  (value) =>
                    res.items.findIndex((old) => old.tplId === value.uid) ===
                    -1
                )
                .map((item) => {
                  return {
                    tplId: item.uid,
                    resId: resId,
                    resType: target,
                    type: "discussion",
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
                    abilities: NO_ABILITIES,
                    newTpl: true,
                    createdAt: item.created_at,
                    updatedAt: item.updated_at,
                  };
                });
            }
          }

          if (typeof onReady !== "undefined") {
            onReady();
          }
          return {
            total: res.total,
            success: true,
            data: [...topicTpl, ...res.items],
          };
        }}
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
          pageSize: pageSize,
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
                    <StatusBadge
                      count={activeNumber}
                      active={activeKey === "active"}
                    />
                  </span>
                ),
              },
              {
                key: "close",
                label: (
                  <span>
                    close
                    <StatusBadge
                      count={closeNumber}
                      active={activeKey === "close"}
                    />
                  </span>
                ),
              },
            ],
            onChange(key) {
              setActiveKey(key as TDiscussionStatus);
              ref.current?.reload();
            },
          },
        }}
      />

      {creatableTypes.length > 0 ? (
        <DiscussionCreate
          resId={resId}
          resType={target}
          type={type}
          creatableTypes={creatableTypes}
          onCreated={() => {
            if (typeof onItemCountChange !== "undefined") {
              onItemCountChange(activeNumber + 1);
            }
            ref.current?.reload();
          }}
        />
      ) : undefined}
    </>
  );
};

export default DiscussionListCardWidget;
