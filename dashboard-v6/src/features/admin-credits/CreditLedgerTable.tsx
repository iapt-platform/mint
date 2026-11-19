// src/features/admin-credits/CreditLedgerTable.tsx
/**
 * 积分流水：最新的在前。给了 account 就只看这个账号（账号抽屉里用），否则是全站流水，可按类型 / 来源 / 日期筛选。
 * 提问扣费那几行点「查看」打开研究过程（复用 AI 对话统计的抽屉）。
 */
import { useState } from "react";
import { Button, DatePicker, Space, Table, Tag, Tooltip, Typography } from "antd";
import type { ColumnsType } from "antd/es/table";
import { UserOutlined } from "@ant-design/icons";
import dayjs, { type Dayjs } from "dayjs";
import type { CreditLedgerEntry, CreditLedgerQuery } from "../../api/adminCredits";
import { useCreditLedger } from "../../hooks/useAdminCredits";
import AgentRunDrawer from "../admin-stats/AgentRunDrawer";
import { useT } from "../admin-stats/helpers";

const KIND_COLOR: Record<string, string> = { earn: "green", spend: "orange", refund: "blue", adjust: "purple" };
const KINDS = ["earn", "spend", "refund", "adjust"] as const;
const SOURCES = ["reading", "editing", "agent_run", "admin"] as const;

interface Props {
  account?: { type: "user" | "guest"; id: string };
  version: number;
}

const CreditLedgerTable = ({ account, version }: Props) => {
  const t = useT();
  const [page, setPage] = useState(1);
  const [kind, setKind] = useState<(typeof KINDS)[number]>();
  const [source, setSource] = useState<(typeof SOURCES)[number]>();
  const [range, setRange] = useState<[Dayjs, Dayjs] | null>(null);
  const [runId, setRunId] = useState<string | null>(null);
  const query: CreditLedgerQuery = {
    page,
    per_page: 20,
    ...(account ? { "filter[account_type]": account.type, "filter[account_id]": account.id } : {}),
    ...(kind ? { "filter[kind]": kind } : {}),
    ...(source ? { "filter[source]": source } : {}),
    ...(range ? { "filter[from]": range[0].format("YYYY-MM-DD"), "filter[to]": range[1].format("YYYY-MM-DD") } : {}),
  };
  const { data, loading } = useCreditLedger(query, version);

  const columns: ColumnsType<CreditLedgerEntry> = [
    {
      title: t("admin.credits.ledger.time"),
      dataIndex: "created_at",
      width: 130,
      render: (v: string, r) => (
        <Tooltip title={`${t("admin.credits.ledger.day")} ${r.day} (UTC)`}>{dayjs(v).format("MM-DD HH:mm")}</Tooltip>
      ),
    },
    ...(account
      ? []
      : [
          {
            title: t("admin.credits.account"),
            dataIndex: "account_id",
            width: 150,
            render: (_: string, r: CreditLedgerEntry) =>
              r.user ? (
                <Tag color="blue">{r.user.nickName}</Tag>
              ) : (
                <Tooltip title={`${r.account_id} · ${r.client_ip ?? ""}`}>
                  <Tag>{t("admin.stats.runs.guest")}</Tag>
                </Tooltip>
              ),
          },
        ]),
    {
      title: t("admin.credits.ledger.delta"),
      dataIndex: "delta",
      width: 90,
      align: "right",
      render: (v: number) => (
        <Typography.Text type={v < 0 ? "danger" : "success"} strong>
          {v > 0 ? `+${v}` : v}
        </Typography.Text>
      ),
    },
    {
      title: t("admin.credits.ledger.kind"),
      dataIndex: "kind",
      width: 90,
      filters: KINDS.map((v) => ({ value: v, text: t(`admin.credits.kind.${v}`) })),
      filterMultiple: false,
      render: (v: string) => <Tag color={KIND_COLOR[v]}>{t(`admin.credits.kind.${v}`)}</Tag>,
    },
    {
      title: t("admin.credits.ledger.bucket"),
      dataIndex: "bucket",
      width: 100,
      render: (v: string) => t(`admin.credits.bucket.${v}`),
    },
    {
      title: t("admin.credits.ledger.source"),
      dataIndex: "source",
      width: 110,
      filters: SOURCES.map((v) => ({ value: v, text: t(`admin.credits.source.${v}`) })),
      filterMultiple: false,
      render: (v: string) => t(`admin.credits.source.${v}`),
    },
    {
      title: t("admin.credits.ledger.note"),
      dataIndex: "note",
      ellipsis: true,
      render: (_: string, r) => (
        <Space size={4} wrap>
          {r.operator && (
            <Tooltip title={t("admin.credits.ledger.operator")}>
              <Tag icon={<UserOutlined />}>{r.operator.nickName}</Tag>
            </Tooltip>
          )}
          {r.note && <span>{r.note === "deep" ? t("admin.credits.ledger.deep") : r.note}</span>}
          {r.source === "agent_run" && (
            <Button size="small" type="link" onClick={() => setRunId(r.ref ?? null)}>
              {t("admin.credits.ledger.run")}
            </Button>
          )}
        </Space>
      ),
    },
  ];

  return (
    <>
      <Space style={{ marginBottom: 8 }} wrap>
        <DatePicker.RangePicker
          value={range}
          allowClear
          onChange={(v) => {
            setRange(v && v[0] && v[1] ? [v[0], v[1]] : null);
            setPage(1);
          }}
        />
        <Typography.Text type="secondary" style={{ fontSize: 12 }}>
          {t("admin.credits.ledger.utc")}
        </Typography.Text>
      </Space>
      <Table<CreditLedgerEntry>
        size="small"
        rowKey={(r) => `${r.account_type}:${r.account_id}:${r.source}:${r.ref}:${r.kind}:${r.bucket}`}
        columns={columns}
        dataSource={data?.data ?? []}
        loading={loading}
        scroll={{ x: 800 }}
        pagination={{
          current: page,
          pageSize: 20,
          total: data?.meta?.total ?? 0,
          showSizeChanger: false,
          onChange: setPage,
        }}
        onChange={(_, f, __, extra) => {
          if (extra.action !== "filter") return;
          setKind((f.kind?.[0] as (typeof KINDS)[number]) ?? undefined);
          setSource((f.source?.[0] as (typeof SOURCES)[number]) ?? undefined);
          setPage(1);
        }}
      />
      <AgentRunDrawer runId={runId} onClose={() => setRunId(null)} />
    </>
  );
};

export default CreditLedgerTable;
