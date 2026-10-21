import { useEffect, useState } from "react";
import { Button, Segmented, Select, Space, Tag, Typography, theme } from "antd";
import { ProList } from "@ant-design/pro-components";

import {
  fetchTextTopics,
  SEVERITY_OPTIONS,
  THREAD_TYPES,
  type IComment,
  type TDiscussionStatus,
  type TDiscussionType,
  type TTextTopicQuery,
} from "../../api/discussion";
import { fetchChannel } from "../../api/channel";
import DiscussionAuthors from "./DiscussionAuthors";
import { SeverityTag } from "./SeverityTag";
import TimeShow from "../general/TimeShow";

const { Text } = Typography;

/** 可选的类型：评论线程 + 批注（批注不在默认里，要显式选） */
const TYPE_OPTIONS: TDiscussionType[] = [...THREAD_TYPES, "note", "commentary"];

/** 选了这些类型时才出现 severity 过滤：只有它们有 severity */
const SEVERITY_VALUES = [
  ...new Set(Object.values(SEVERITY_OPTIONS).flat()),
] as string[];

type TStatusFilter = TDiscussionStatus | "all";

interface IWidget {
  book: number;
  /** 章节起始段落号 */
  chapter: number;
  /** 页面打开的版本（URL 里 channel=a_b 拆开）；讨论挂在句子上，句子属于某个版本 */
  channels: string[];
  /** 点某条话题：调用方切到那一句并打开话题 */
  onOpen?: (topic: IComment) => void;
  /** 点某一行：选中它（再点一次取消，传 undefined），调用方让正文滚到那一句并高亮 */
  onSelect?: (topic?: IComment) => void;
}

/**
 * 一章里的全部讨论（`GET /v3/tipitaka-discussions/{channel}?book=&chapter=`），按经文顺序。
 *
 * 过滤：版本（多版本时选一个，必选）、类型（多选，不选只列话题）、状态、严重程度。
 * 点一行：选中并让正文滚到那句、高亮；点标题：打开话题。
 * 话题详情与回复不在这里做：点开交给调用方切到「本句」视图，复用单句讨论那一套。
 */
const ChapterDiscussionList = ({
  book,
  chapter,
  channels,
  onOpen,
  onSelect,
}: IWidget) => {
  const { token } = theme.useToken();
  const [selectedId, setSelectedId] = useState<string>();
  const [channel, setChannel] = useState<string | undefined>(channels[0]);
  const [channelNames, setChannelNames] = useState<Record<string, string>>({});
  const [types, setTypes] = useState<TDiscussionType[]>([]);
  const [status, setStatus] = useState<TStatusFilter>("active");
  const [severity, setSeverity] = useState<string[]>([]);

  // URL 里的版本变了：当前选中的不在里面就换成第一个
  const [prevChannels, setPrevChannels] = useState(channels.join("_"));
  if (prevChannels !== channels.join("_")) {
    setPrevChannels(channels.join("_"));
    if (!channel || !channels.includes(channel)) {
      setChannel(channels[0]);
    }
  }

  // 多版本时下拉框要显示名字
  useEffect(() => {
    if (channels.length < 2) return;
    let alive = true;
    Promise.all(
      channels.map(async (id) => [id, (await fetchChannel(id))?.name ?? id])
    ).then((pairs) => {
      if (alive) setChannelNames(Object.fromEntries(pairs));
    });
    return () => {
      alive = false;
    };
  }, [channels]);

  const showSeverity =
    types.length === 0 ||
    types.some((t) => (SEVERITY_OPTIONS[t] ?? []).length > 0);

  if (!channel) {
    return (
      <Typography.Paragraph type="secondary">
        先在「版本」里选一个译文版本，再看本章的讨论。
      </Typography.Paragraph>
    );
  }

  return (
    <ProList<IComment>
      rowKey="id"
      onRow={(entity) => ({
        onClick: () => {
          const next = entity.id === selectedId ? undefined : entity;
          setSelectedId(next?.id);
          onSelect?.(next);
        },
        style: {
          cursor: "pointer",
          backgroundColor:
            entity.id === selectedId ? token.controlItemBgActive : undefined,
        },
      })}
      params={{ channel, book, chapter, types, status, severity }}
      request={async (params = {}) => {
        const query: TTextTopicQuery = {
          book,
          chapter,
          page: params.current ?? 1,
          per_page: params.pageSize ?? 20,
        };
        if (types.length > 0) query.type = types.join(",");
        if (status !== "all") query.status = status;
        if (showSeverity && severity.length > 0) {
          query.severity = severity.join(",");
        }
        const res = await fetchTextTopics(channel, query);
        return { data: res.items, total: res.total, success: true };
      }}
      headerTitle={
        <Space wrap size={[8, 8]}>
          {channels.length > 1 ? (
            <Select
              size="small"
              style={{ minWidth: 140 }}
              value={channel}
              onChange={setChannel}
              options={channels.map((id) => ({
                value: id,
                label: channelNames[id] ?? id.slice(0, 8),
              }))}
            />
          ) : undefined}
          <Select<TDiscussionType[]>
            size="small"
            mode="multiple"
            allowClear
            style={{ minWidth: 160 }}
            placeholder="类型：全部话题"
            value={types}
            onChange={setTypes}
            options={TYPE_OPTIONS.map((value) => ({ value, label: value }))}
          />
          <Segmented<TStatusFilter>
            size="small"
            value={status}
            onChange={setStatus}
            options={[
              { value: "active", label: "active" },
              { value: "close", label: "close" },
              { value: "all", label: "全部" },
            ]}
          />
          {showSeverity ? (
            <Select<string[]>
              size="small"
              mode="multiple"
              allowClear
              style={{ minWidth: 120 }}
              placeholder="严重程度"
              value={severity}
              onChange={setSeverity}
              options={SEVERITY_VALUES.map((value) => ({ value, label: value }))}
            />
          ) : undefined}
        </Space>
      }
      metas={{
        avatar: {
          render: (_dom, entity) => (
            <DiscussionAuthors user={entity.user} coEditor={entity.coEditor} />
          ),
        },
        title: {
          render: (_dom, entity) => (
            <div>
              {entity.deleted ? (
                <Text type="secondary" italic>
                  （已删除）
                </Text>
              ) : (
                <Button
                  size="small"
                  type="link"
                  onClick={(e) => {
                    e.stopPropagation(); // 打开话题，不是选中行
                    onOpen?.(entity);
                  }}
                >
                  {entity.title}
                </Button>
              )}
              {entity.type !== "discussion" ? <Tag>{entity.type}</Tag> : undefined}
              <SeverityTag severity={entity.severity} />
              {status === "all" && entity.status === "close" ? (
                <Tag>close</Tag>
              ) : undefined}
            </div>
          ),
        },
        description: {
          render: (_dom, entity) => (
            <Space size={4} wrap>
              {entity.sentence ? (
                <Text type="secondary">
                  §{entity.sentence.paragraph} · {entity.sentence.wordStart}–
                  {entity.sentence.wordEnd}
                </Text>
              ) : undefined}
              {entity.childrenCount ? (
                <Text type="secondary">{entity.childrenCount} 条回复</Text>
              ) : undefined}
              <TimeShow
                type="secondary"
                showIcon={false}
                createdAt={entity.createdAt}
                updatedAt={entity.updatedAt}
              />
            </Space>
          ),
        },
      }}
      pagination={{ pageSize: 20, showSizeChanger: true, size: "small" }}
      search={false}
      bordered
    />
  );
};

export default ChapterDiscussionList;
