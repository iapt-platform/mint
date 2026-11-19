// src/features/admin-credits/CreditOverview.tsx
/**
 * 积分概览 + 结算任务面板。
 *
 * 每日任务 `credits:settle-activity` 出问题时，在这里手工补：
 * - 「补齐漏结算」与每日任务完全相同：从积分上线日到昨天，找出有活动却没结清的「用户 + 日」重新结算；
 * - 「重算某一天」按当前策略重算那天所有用户。
 * 两者都可以重复执行：同一用户同一天的积分是覆盖不是累加，不会重复入账。
 */
import { useState } from "react";
import { Alert, Button, Card, Col, DatePicker, Descriptions, Popconfirm, Row, Space, Statistic, Table, Tag, Typography, message } from "antd";
import { SyncOutlined } from "@ant-design/icons";
import dayjs, { type Dayjs } from "dayjs";
import { settleCredits, type CreditOverview as Overview, type CreditSettlement } from "../../api/adminCredits";
import { useT } from "../admin-stats/helpers";

interface Props {
  data: Overview | null;
  loading: boolean;
  onSettled: () => void;
}

type Day = NonNullable<Overview["recent_days"]>[number];

const CreditOverview = ({ data, loading, onSettled }: Props) => {
  const t = useT();
  const [running, setRunning] = useState<"missing" | "day" | null>(null);
  const [day, setDay] = useState<Dayjs | null>(null);

  const run = async (mode: "missing" | "day") => {
    setRunning(mode);
    try {
      const r = await settleCredits(mode === "day" && day ? { date: day.format("YYYY-MM-DD") } : {});
      message.success(
        r.mode === "day"
          ? t("admin.credits.settle.done.day", { day: r.from, users: r.users })
          : t("admin.credits.settle.done.missing", { users: r.users, pairs: r.pairs ?? 0 }),
      );
      onSettled();
    } finally {
      setRunning(null);
    }
  };

  const last = data?.last_settlement as CreditSettlement | null | undefined;
  const totals = data?.totals;
  const policy = data?.policy as
    | { daily_free?: { user?: number; guest?: number }; cost?: { normal?: number; deep?: number } }
    | undefined;
  // 今天之前、某天没有任何人拿到积分：每日任务可能没跑（也可能那天确实没人读经，自己判断）
  const today = data?.today;
  const empty = (r: Day) => r.day !== today && r.users === 0;

  return (
    <Space orientation="vertical" size="middle" style={{ width: "100%" }}>
      <Row gutter={[12, 12]}>
        {(
          [
            ["outstanding_balance", "admin.credits.total.balance"],
            ["earned_total", "admin.credits.total.earned"],
            ["adjusted_total", "admin.credits.total.adjusted"],
            ["spent_today", "admin.credits.total.spent_today"],
            ["user_accounts", "admin.credits.total.users"],
            ["guest_accounts", "admin.credits.total.guests"],
          ] as const
        ).map(([key, label]) => (
          <Col key={key} xs={12} sm={8} lg={4}>
            <Card size="small" loading={loading}>
              <Statistic title={t(label)} value={totals?.[key] ?? 0} />
            </Card>
          </Col>
        ))}
      </Row>

      <Card
        size="small"
        title={t("admin.credits.settle.title")}
        loading={loading && !data}
        extra={
          policy && (
            <Typography.Text type="secondary" style={{ fontSize: 12 }}>
              {t("admin.credits.policy", {
                version: data?.policy_version ?? "",
                user: policy.daily_free?.user ?? 0,
                guest: policy.daily_free?.guest ?? 0,
                normal: policy.cost?.normal ?? 0,
                deep: policy.cost?.deep ?? 0,
              })}
            </Typography.Text>
          )
        }
      >
        <Row gutter={[24, 16]}>
          <Col xs={24} lg={10}>
            <Descriptions size="small" column={1} title={t("admin.credits.settle.last")}>
              {last ? (
                <>
                  <Descriptions.Item label={t("admin.credits.settle.ran_at")}>
                    {dayjs(last.ran_at).format("YYYY-MM-DD HH:mm:ss")}
                  </Descriptions.Item>
                  <Descriptions.Item label={t("admin.credits.settle.trigger")}>
                    {last.trigger === "cli" ? (
                      <Tag>{t("admin.credits.settle.trigger.cli")}</Tag>
                    ) : (
                      <Tag color="purple">{t("admin.credits.settle.trigger.admin", { name: last.operator ?? "" })}</Tag>
                    )}
                  </Descriptions.Item>
                  <Descriptions.Item label={t("admin.credits.settle.result")}>
                    {last.mode === "day"
                      ? t("admin.credits.settle.done.day", { day: last.from, users: last.users })
                      : t("admin.credits.settle.done.missing", { users: last.users, pairs: last.pairs ?? 0 })}
                    <Typography.Text type="secondary"> · {last.seconds}s</Typography.Text>
                  </Descriptions.Item>
                </>
              ) : (
                <Descriptions.Item>
                  <Typography.Text type="secondary">{t("admin.credits.settle.never")}</Typography.Text>
                </Descriptions.Item>
              )}
            </Descriptions>

            <Alert type="info" showIcon style={{ margin: "12px 0" }} message={t("admin.credits.settle.safe")} />

            <Space orientation="vertical" style={{ width: "100%" }}>
              <Popconfirm
                title={t("admin.credits.settle.missing")}
                description={t("admin.credits.settle.missing.tip", { since: data?.earn_since ?? "" })}
                onConfirm={() => run("missing")}
              >
                <Button type="primary" icon={<SyncOutlined spin={running === "missing"} />} loading={running === "missing"} block>
                  {t("admin.credits.settle.missing")}
                </Button>
              </Popconfirm>
              <Space.Compact style={{ width: "100%" }}>
                <DatePicker
                  value={day}
                  onChange={setDay}
                  style={{ flex: 1 }}
                  placeholder={t("admin.credits.settle.day.placeholder")}
                  disabledDate={(d) =>
                    d.isAfter(dayjs().endOf("day")) || (!!data?.earn_since && d.isBefore(dayjs(data.earn_since), "day"))
                  }
                />
                <Popconfirm
                  title={t("admin.credits.settle.day")}
                  description={t("admin.credits.settle.day.tip")}
                  disabled={!day}
                  onConfirm={() => run("day")}
                >
                  <Button disabled={!day} loading={running === "day"}>
                    {t("admin.credits.settle.day")}
                  </Button>
                </Popconfirm>
              </Space.Compact>
            </Space>
          </Col>
          <Col xs={24} lg={14}>
            <Typography.Text strong>{t("admin.credits.recent.title")}</Typography.Text>
            <Table<Day>
              size="small"
              rowKey="day"
              style={{ marginTop: 8 }}
              pagination={false}
              dataSource={data?.recent_days ?? []}
              columns={[
                {
                  title: t("admin.credits.recent.day"),
                  dataIndex: "day",
                  render: (v: string, r) => (
                    <Space size={4}>
                      {v}
                      {v === today && <Tag>{t("admin.credits.recent.today")}</Tag>}
                      {empty(r) && <Tag color="warning">{t("admin.credits.recent.empty")}</Tag>}
                    </Space>
                  ),
                },
                { title: t("admin.credits.recent.users"), dataIndex: "users", align: "right" },
                { title: t("admin.credits.source.reading"), dataIndex: "reading", align: "right" },
                { title: t("admin.credits.source.editing"), dataIndex: "editing", align: "right" },
              ]}
            />
          </Col>
        </Row>
      </Card>
    </Space>
  );
};

export default CreditOverview;
