// src/features/admin-stats/AgentRunTable.tsx
/**
 * 提问记录：最新的在前，可按状态 / 路由 / 账号类型筛选、按提问原文搜索，点一行看研究过程。
 * 数据来自 GET /v3/admin/agent-runs（提问原文对 root / administrator 可见）。
 */
import { useState } from "react";
import { Badge, Card, Input, Table, Tag, Tooltip, Typography } from "antd";
import type { ColumnsType } from "antd/es/table";
import { BulbFilled, CheckOutlined, MinusOutlined } from "@ant-design/icons";
import dayjs from "dayjs";
import type { AgentRoute, AgentRun } from "../../api/adminStats";
import { useAgentRuns } from "../../hooks/useAdminStats";
import AgentRunDrawer from "./AgentRunDrawer";
import { ROUTES, useT } from "./helpers";

const STATUS_BADGE: Record<string, "success" | "warning" | "error" | "default"> = {
  ok: "success",
  denied: "warning",
  error: "error",
  cancelled: "default",
};
const ROUTE_COLOR: Record<string, string> = { question: "blue", term: "purple", passage: "cyan", chat: "default" };

type Filters = { status?: AgentRun["status"]; route?: AgentRoute; account_type?: "user" | "guest" };

const AgentRunTable = () => {
  const t = useT();
  const [page, setPage] = useState(1);
  const [q, setQ] = useState("");
  const [filters, setFilters] = useState<Filters>({});
  const [runId, setRunId] = useState<string | null>(null);
  const { data, loading } = useAgentRuns({
    page,
    per_page: 20,
    ...(q ? { "filter[q]": q } : {}),
    ...(filters.status ? { "filter[status]": filters.status as "ok" } : {}),
    ...(filters.route ? { "filter[route]": filters.route } : {}),
    ...(filters.account_type ? { "filter[account_type]": filters.account_type } : {}),
  });

  const columns: ColumnsType<AgentRun> = [
    {
      title: t("admin.stats.runs.time"),
      dataIndex: "started_at",
      width: 120,
      render: (v?: string | null) => (v ? dayjs(v).format("MM-DD HH:mm") : "—"),
    },
    {
      title: t("admin.stats.runs.question"),
      dataIndex: "question",
      ellipsis: true,
      render: (v: string) => <Tooltip title={v}>{v}</Tooltip>,
    },
    {
      title: t("admin.stats.runs.account"),
      dataIndex: "account_type",
      width: 130,
      filters: (["user", "guest"] as const).map((v) => ({ value: v, text: t(`admin.stats.account.${v}`) })),
      filterMultiple: false,
      render: (_: string, r) =>
        r.user ? (
          <Tag color="blue">{r.user.nickName}</Tag>
        ) : (
          <Tooltip title={`${r.device_id ?? ""} · ${r.client_ip ?? ""}`}>
            <Tag>{t("admin.stats.runs.guest")}</Tag>
          </Tooltip>
        ),
    },
    { title: t("admin.stats.runs.client"), dataIndex: "client", width: 80 },
    {
      title: t("admin.stats.runs.route"),
      dataIndex: "route",
      width: 120,
      filters: ROUTES.map((v) => ({ value: v, text: t(`admin.stats.route.${v}`) })),
      filterMultiple: false,
      render: (v?: string | null) => <Tag color={ROUTE_COLOR[v ?? ""]}>{t(`admin.stats.route.${v ?? "none"}`)}</Tag>,
    },
    {
      title: t("admin.stats.runs.deep"),
      dataIndex: "deep",
      width: 60,
      render: (v: boolean) => (v ? <BulbFilled style={{ color: "#faad14" }} /> : null),
    },
    {
      title: t("admin.stats.runs.latency"),
      dataIndex: "latency_ms",
      width: 90,
      align: "right",
      render: (v?: number | null) => (v != null ? `${(v / 1000).toFixed(1)} ${t("admin.stats.ai.seconds")}` : "—"),
    },
    {
      title: t("admin.stats.runs.status"),
      dataIndex: "status",
      width: 110,
      filters: ["ok", "denied", "error", "cancelled"].map((v) => ({ value: v, text: t(`admin.stats.status.${v}`) })),
      filterMultiple: false,
      render: (v: string) => <Badge status={STATUS_BADGE[v] ?? "default"} text={t(`admin.stats.status.${v}`)} />,
    },
    {
      title: <Tooltip title={t("admin.stats.ai.improve.tip")}>{t("admin.stats.runs.improve")}</Tooltip>,
      dataIndex: "improve",
      width: 70,
      render: (v: boolean) => (v ? <CheckOutlined /> : <MinusOutlined style={{ opacity: 0.4 }} />),
    },
  ];

  return (
    <Card
      size="small"
      variant="borderless"
      styles={{ header: { paddingInline: 0 }, body: { paddingInline: 0 } }}
      extra={
        <Input.Search
          allowClear
          placeholder={t("admin.stats.runs.search")}
          onSearch={(v) => {
            setQ(v.trim());
            setPage(1);
          }}
          style={{ width: 240, maxWidth: "100%" }}
        />
      }
    >
      <Table<AgentRun>
        size="small"
        rowKey="run_id"
        columns={columns}
        dataSource={data?.data ?? []}
        loading={loading}
        scroll={{ x: 900 }}
        pagination={{
          current: page,
          pageSize: 20,
          total: data?.meta?.total ?? 0,
          showSizeChanger: false,
          onChange: setPage,
        }}
        onChange={(_, f, __, extra) => {
          // 翻页也会触发 onChange：只在筛选时重置筛选条件与页码
          if (extra.action !== "filter") return;
          setFilters({
            account_type: (f.account_type?.[0] as Filters["account_type"]) ?? undefined,
            route: (f.route?.[0] as AgentRoute | undefined) ?? undefined,
            status: (f.status?.[0] as Filters["status"]) ?? undefined,
          });
          setPage(1);
        }}
        onRow={(r) => ({ onClick: () => setRunId(r.run_id ?? null), style: { cursor: "pointer" } })}
      />
      <Typography.Text type="secondary" style={{ fontSize: 12 }}>
        agent_runs · agent_threads
      </Typography.Text>
      <AgentRunDrawer runId={runId} onClose={() => setRunId(null)} />
    </Card>
  );
};

export default AgentRunTable;
