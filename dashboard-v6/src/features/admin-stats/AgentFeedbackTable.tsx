// src/features/admin-stats/AgentFeedbackTable.tsx
/**
 * 用户评价审阅：最新的在前，可按赞 / 踩、标签或原因、有无文字说明筛选。
 * 每条带对应提问的摘要，点一行看研究过程。
 * 数据来自 GET /v3/admin/agent-feedback（不含账号身份）。
 */
import { useState } from "react";
import { Space, Table, Tag, Tooltip, Typography } from "antd";
import type { ColumnsType } from "antd/es/table";
import { DislikeFilled, LikeFilled } from "@ant-design/icons";
import dayjs from "dayjs";
import type { AgentFeedbackItem } from "../../api/adminStats";
import { useAgentFeedback } from "../../hooks/useAdminStats";
import AgentRunDrawer from "./AgentRunDrawer";

const PAGE_SIZE = 20;
import { useT } from "./helpers";

const UP_TAGS = ["accurate", "useful_citations", "clear", "inspiring"];
const DOWN_REASONS = ["citation_wrong", "missed", "off_topic", "incorrect", "too_long", "too_academic", "slow", "other"];

type Filters = { rating?: 1 | -1; tag?: string };

const AgentFeedbackTable = () => {
  const t = useT();
  const [page, setPage] = useState(1);
  const [filters, setFilters] = useState<Filters>({});
  const [runId, setRunId] = useState<string | null>(null);
  const { data, loading } = useAgentFeedback({
    page,
    per_page: PAGE_SIZE,
    ...(filters.rating ? { "filter[rating]": String(filters.rating) as "1" | "-1" } : {}),
    ...(filters.tag ? { "filter[tag]": filters.tag } : {}),
  });

  // 评价没有对外的 id（接口不返回自增主键，也没有按单条评价查询的接口）：
  // 表格只读，行 key 用「分页偏移 + 页内序号」即可
  const rows = (data?.data ?? []).map((item, i) => ({ ...item, key: (page - 1) * PAGE_SIZE + i }));

  const columns: ColumnsType<AgentFeedbackItem> = [
    {
      title: t("admin.stats.runs.time"),
      dataIndex: "updated_at",
      width: 120,
      render: (v?: string) => (v ? dayjs(v).format("MM-DD HH:mm") : "—"),
    },
    {
      title: t("admin.stats.feedback.rating"),
      dataIndex: "rating",
      width: 80,
      filters: [
        { value: 1, text: t("admin.stats.feedback.up") },
        { value: -1, text: t("admin.stats.feedback.down") },
      ],
      filterMultiple: false,
      render: (v: number, r) => (
        <Space size={4}>
          {v > 0 ? <LikeFilled style={{ color: "#52c41a" }} /> : <DislikeFilled style={{ color: "#ff4d4f" }} />}
          {r.asked ? (
            <Tooltip title={t("admin.stats.feedback.asked.tip")}>
              <Tag>{t("admin.stats.feedback.asked")}</Tag>
            </Tooltip>
          ) : null}
        </Space>
      ),
    },
    {
      title: t("admin.stats.feedback.tags"),
      dataIndex: "tags",
      width: 260,
      filters: [...UP_TAGS, ...DOWN_REASONS].map((v) => ({ value: v, text: t(`admin.stats.feedback.tag.${v}`) })),
      filterMultiple: false,
      render: (tags: string[] | undefined, r) => (
        <div>
          <Space size={4} wrap>
            {(tags ?? []).map((tag) => (
              <Tag key={tag} color={r.rating && r.rating > 0 ? "green" : "volcano"}>
                {t(`admin.stats.feedback.tag.${tag}`)}
              </Tag>
            ))}
          </Space>
          {r.comment ? (
            <Typography.Paragraph style={{ margin: "4px 0 0", fontSize: 12 }} ellipsis={{ rows: 2, tooltip: r.comment }}>
              {r.comment}
            </Typography.Paragraph>
          ) : null}
        </div>
      ),
    },
    {
      title: t("admin.stats.runs.question"),
      dataIndex: "question",
      ellipsis: true,
      render: (v?: string | null) => v ?? <Typography.Text type="secondary">{t("admin.stats.feedback.no_run")}</Typography.Text>,
    },
    {
      title: t("admin.stats.runs.route"),
      dataIndex: "route",
      width: 110,
      render: (v?: string | null, r?: AgentFeedbackItem) => (r?.run_id ? t(`admin.stats.route.${v ?? "none"}`) : null),
    },
    { title: t("admin.stats.runs.account"), dataIndex: "account_type", width: 80, render: (v: string) => t(`admin.stats.account.${v}`) },
    { title: t("admin.stats.runs.client"), dataIndex: "client", width: 80 },
  ];

  return (
    <>
      <Table<AgentFeedbackItem>
        size="small"
        columns={columns}
        dataSource={rows}
        loading={loading}
        scroll={{ x: 1000 }}
        pagination={{ current: page, pageSize: PAGE_SIZE, total: data?.meta?.total ?? 0, showSizeChanger: false, onChange: setPage }}
        onChange={(_, f, __, extra) => {
          // 翻页也会触发 onChange：只在筛选时重置筛选条件与页码
          if (extra.action !== "filter") return;
          setFilters({
            rating: (f.rating?.[0] as 1 | -1 | undefined) ?? undefined,
            tag: (f.tags?.[0] as string | undefined) ?? undefined,
          });
          setPage(1);
        }}
        onRow={(r) => ({
          onClick: () => r.run_id && setRunId(r.run_id),
          style: { cursor: r.run_id ? "pointer" : "default" },
        })}
      />
      <Typography.Text type="secondary" style={{ fontSize: 12 }}>
        agent_feedback · agent_runs
      </Typography.Text>
      <AgentRunDrawer runId={runId} onClose={() => setRunId(null)} />
    </>
  );
};

export default AgentFeedbackTable;
