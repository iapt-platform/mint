// src/features/admin-credits/CreditAccountTable.tsx
/**
 * 有积分流水的账号一览：余额、累计赚取 / 调整 / 消耗、今天用掉的赠送额度。
 * 点一行看这个账号的流水；登录用户可直接加减积分。
 */
import { useState } from "react";
import { Button, Drawer, Input, Table, Tag, Tooltip, Typography } from "antd";
import type { ColumnsType } from "antd/es/table";
import dayjs from "dayjs";
import type { CreditAccount } from "../../api/adminCredits";
import { useCreditAccounts } from "../../hooks/useAdminCredits";
import { useT } from "../admin-stats/helpers";
import CreditLedgerTable from "./CreditLedgerTable";

interface Props {
  version: number;
  onAdjust: (account: CreditAccount) => void;
}

const accountName = (r: CreditAccount) => r.user?.nickName ?? (r.account_id ?? "").slice(0, 8);

const CreditAccountTable = ({ version, onAdjust }: Props) => {
  const t = useT();
  const [page, setPage] = useState(1);
  const [q, setQ] = useState("");
  const [type, setType] = useState<"user" | "guest">();
  const [selected, setSelected] = useState<CreditAccount | null>(null);
  const { data, loading } = useCreditAccounts(
    {
      page,
      per_page: 20,
      ...(q ? { "filter[q]": q } : {}),
      ...(type ? { "filter[account_type]": type } : {}),
    },
    version,
  );

  const columns: ColumnsType<CreditAccount> = [
    {
      title: t("admin.credits.account"),
      dataIndex: "account_id",
      filters: (["user", "guest"] as const).map((v) => ({ value: v, text: t(`admin.stats.account.${v}`) })),
      filterMultiple: false,
      render: (_: string, r) =>
        r.user ? (
          <span>
            <Tag color="blue">{r.user.nickName}</Tag>
            <Typography.Text type="secondary">@{r.user.userName}</Typography.Text>
          </span>
        ) : (
          <Tooltip title={r.account_id}>
            <Tag>{t("admin.stats.runs.guest")}</Tag>
            <Typography.Text type="secondary" code>
              {(r.account_id ?? "").slice(0, 8)}
            </Typography.Text>
          </Tooltip>
        ),
    },
    {
      title: <Tooltip title={t("admin.credits.balance.tip")}>{t("admin.credits.balance")}</Tooltip>,
      dataIndex: "earned_balance",
      width: 100,
      align: "right",
      render: (v: number, r) => (r.account_type === "user" ? <Typography.Text strong>{v}</Typography.Text> : "—"),
    },
    { title: t("admin.credits.earned"), dataIndex: "earned_total", width: 100, align: "right" },
    { title: t("admin.credits.adjusted"), dataIndex: "adjusted_total", width: 100, align: "right" },
    { title: t("admin.credits.spent"), dataIndex: "spent_total", width: 100, align: "right" },
    {
      title: <Tooltip title={t("admin.credits.free_today.tip")}>{t("admin.credits.free_today")}</Tooltip>,
      dataIndex: "free_used_today",
      width: 120,
      align: "right",
      render: (v: number, r) => `${v} / ${r.daily_free}`,
    },
    {
      title: t("admin.credits.last_activity"),
      dataIndex: "last_activity_at",
      width: 120,
      render: (v?: string | null) => (v ? dayjs(v).format("MM-DD HH:mm") : "—"),
    },
    {
      title: "",
      key: "actions",
      width: 90,
      render: (_, r) =>
        r.account_type === "user" ? (
          <Button
            size="small"
            onClick={(e) => {
              e.stopPropagation();
              onAdjust(r);
            }}
          >
            {t("admin.credits.adjust.short")}
          </Button>
        ) : null,
    },
  ];

  return (
    <>
      <Input.Search
        allowClear
        placeholder={t("admin.credits.search")}
        onSearch={(v) => {
          setQ(v.trim());
          setPage(1);
        }}
        style={{ width: 280, maxWidth: "100%", marginBottom: 8 }}
      />
      <Table<CreditAccount>
        size="small"
        rowKey={(r) => `${r.account_type}:${r.account_id}`}
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
          if (extra.action !== "filter") return;
          setType((f.account_id?.[0] as "user" | "guest") ?? undefined);
          setPage(1);
        }}
        onRow={(r) => ({ onClick: () => setSelected(r), style: { cursor: "pointer" } })}
      />
      <Drawer
        title={selected ? t("admin.credits.ledger.of", { name: accountName(selected) }) : ""}
        open={!!selected}
        onClose={() => setSelected(null)}
        size={1040}
        extra={
          selected?.account_type === "user" && (
            <Button type="primary" onClick={() => onAdjust(selected)}>
              {t("admin.credits.adjust.short")}
            </Button>
          )
        }
      >
        {selected?.account_type && selected.account_id && (
          <CreditLedgerTable account={{ type: selected.account_type, id: selected.account_id }} version={version} />
        )}
      </Drawer>
    </>
  );
};

export default CreditAccountTable;
