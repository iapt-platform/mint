// src/features/admin-stats/AgentStats.tsx
/**
 * 管理后台「AI 对话统计」：指标卡 → 每日提问（按研究路径）+ 耗时分位数 → 工具 / 积分 / token → 提问记录。
 * 数据来自 GET /v3/admin/agent-stats 与 /v3/admin/agent-runs。
 */
import { useState } from "react";
import { Button, Card, Col, Empty, Progress, Result, Row, Statistic, Tabs, Tooltip, Typography } from "antd";
import { InfoCircleOutlined } from "@ant-design/icons";
import { Bar, Column, Line, Tiny } from "@ant-design/plots";
import KpiCard from "../../components/stats/KpiCard";
import { useAgentStats } from "../../hooks/useAdminStats";
import AgentRunTable from "./AgentRunTable";
import AgentFeedbackTable from "./AgentFeedbackTable";
import { ROUTES, change, pct, tiny, useChartTheme, useStatsRange, useT } from "./helpers";
import { Source, StatsToolbar } from "./shared";

type Account = "all" | "user" | "guest";
const CREDIT_KINDS = ["spent_free", "spent_earned", "refunded", "earned_reading", "earned_editing", "adjusted"] as const;

const AgentStats = () => {
  const t = useT();
  const chartTheme = useChartTheme();
  const { range, setRange, query } = useStatsRange();
  const [account, setAccount] = useState<Account>("all");
  const { data, loading, error, refresh } = useAgentStats({ ...query, account_type: account });

  const base = { theme: chartTheme, autoFit: true } as const;
  const s = data?.summary;
  const p = data?.previous;
  const daily = data?.daily ?? [];
  const runs = s?.runs ?? 0;
  const seconds = (ms?: number | null) => (ms == null ? null : Number((ms / 1000).toFixed(1)));

  const toolbar = (
    <StatsToolbar<Account>
      title={t("admin.stats.ai.title")}
      segments={(["all", "user", "guest"] as const).map((v) => ({ value: v, label: t(`admin.stats.account.${v}`) }))}
      segment={account}
      onSegment={setAccount}
      range={range}
      onRange={setRange}
    />
  );

  if (error && !data) {
    return (
      <>
        {toolbar}
        <Result
          status="warning"
          title={String((error as Error).message ?? error)}
          extra={<Button onClick={refresh}>{t("buttons.refresh")}</Button>}
        />
      </>
    );
  }

  const perDay = daily.map((d) => ROUTES.reduce((n, r) => n + (d[r] ?? 0), 0) + (d.none ?? 0));
  const stacked = daily.flatMap((d) =>
    [...ROUTES, "none" as const].map((r) => ({ date: d.date?.slice(5), route: t(`admin.stats.route.${r}`), value: d[r] ?? 0 })),
  );
  const latency = daily.flatMap((d) => [
    { date: d.date?.slice(5), value: seconds(d.latency_p50_ms), series: "P50" },
    { date: d.date?.slice(5), value: seconds(d.latency_p95_ms), series: "P95" },
  ]).filter((r) => r.value != null);
  const failed = (s?.denied ?? 0) + (s?.errors ?? 0);
  const prevFailedRatio = p?.runs ? ((p.denied ?? 0) + (p.errors ?? 0)) / p.runs : null;

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
      {toolbar}

      <Row gutter={[16, 16]}>
        <Col xs={24} sm={12} xl={6}>
          <KpiCard
            loading={loading && !data}
            title={t("admin.stats.ai.runs")}
            value={runs}
            change={change(s?.runs, p?.runs)}
            changeLabel={t("admin.stats.compare")}
            chart={<Tiny.Area {...base} height={46} data={tiny(perDay)} xField="index" yField="value" shapeField="smooth" style={{ fillOpacity: 0.3 }} />}
            footer={
              <>
                <span>{t("admin.stats.ai.runs.deep", { ratio: pct(s?.deep ?? 0, runs) })}</span>
                <span>{t("admin.stats.ai.runs.passage", { ratio: pct(s?.passage ?? 0, runs) })}</span>
              </>
            }
            source="agent_runs"
          />
        </Col>
        <Col xs={24} sm={12} xl={6}>
          <KpiCard
            loading={loading && !data}
            title={t("admin.stats.ai.askers")}
            tip={t("admin.stats.ai.askers.tip")}
            value={s?.askers ?? 0}
            change={change(s?.askers, p?.askers)}
            changeLabel={t("admin.stats.compare")}
            chart={
              <Progress
                percent={Number(pct(s?.askers_user ?? 0, s?.askers ?? 0))}
                success={{ percent: Number(pct(s?.askers_user ?? 0, s?.askers ?? 0)) }}
                size="small"
                style={{ marginTop: 12 }}
              />
            }
            footer={<span>{t("admin.stats.ai.askers.split", { user: s?.askers_user ?? 0, guest: s?.askers_guest ?? 0 })}</span>}
            source="agent_threads.account"
          />
        </Col>
        <Col xs={24} sm={12} xl={6}>
          <KpiCard
            loading={loading && !data}
            title={t("admin.stats.ai.latency")}
            value={seconds(s?.latency_p50_ms)}
            suffix={t("admin.stats.ai.seconds")}
            change={change(s?.latency_p50_ms, p?.latency_p50_ms)}
            inverse
            changeLabel={t("admin.stats.compare")}
            chart={
              <Tiny.Line
                {...base}
                height={46}
                data={tiny(daily.map((d) => seconds(d.latency_p50_ms) ?? 0))}
                xField="index"
                yField="value"
              />
            }
            footer={<span>{t("admin.stats.ai.latency.p95", { seconds: seconds(s?.latency_p95_ms) ?? "—" })}</span>}
            source="agent_runs.latency_ms"
          />
        </Col>
        <Col xs={24} sm={12} xl={6}>
          <KpiCard
            loading={loading && !data}
            title={t("admin.stats.ai.failed")}
            tip={t("admin.stats.ai.failed.tip")}
            value={pct(failed, runs)}
            suffix="%"
            change={change(runs ? failed / runs : null, prevFailedRatio)}
            inverse
            changeLabel={t("admin.stats.compare")}
            chart={
              <Tiny.Column
                {...base}
                height={46}
                data={tiny(daily.map((d) => (d.denied ?? 0) + (d.errors ?? 0)))}
                xField="index"
                yField="value"
                style={{ fill: "#faad14" }}
              />
            }
            footer={
              <span>
                {t("admin.stats.ai.failed.split", { denied: s?.denied ?? 0, errors: s?.errors ?? 0, unchecked: s?.unchecked ?? 0 })}
              </span>
            }
            source="agent_runs.status"
          />
        </Col>
      </Row>

      <Row gutter={[16, 16]}>
        <Col xs={24} xl={16}>
          <Card size="small" title={t("admin.stats.ai.daily")} loading={loading && !data}>
            <Column
              {...base}
              height={300}
              data={stacked}
              xField="date"
              yField="value"
              colorField="route"
              stack
              axis={{ x: { labelAutoHide: true } }}
            />
            <Source>agent_runs.route</Source>
          </Card>
        </Col>
        <Col xs={24} xl={8}>
          <Card size="small" title={t("admin.stats.ai.latency.daily")} loading={loading && !data}>
            {latency.length ? (
              <Line {...base} height={300} data={latency} xField="date" yField="value" colorField="series" axis={{ x: { labelAutoHide: true } }} />
            ) : (
              <Empty description={t("admin.stats.empty")} />
            )}
            <Source>agent_runs.latency_ms</Source>
          </Card>
        </Col>
      </Row>

      <Row gutter={[16, 16]}>
        <Col xs={24} md={12} xl={8}>
          <Card size="small" title={t("admin.stats.ai.tools")} loading={loading && !data}>
            {data?.tools?.length ? (
              <Bar
                {...base}
                height={260}
                data={data.tools}
                xField="name"
                yField="calls"
                label={{ text: "calls", position: "right", dx: 4 }}
                axis={{ x: { title: false }, y: false }}
                tooltip={{ items: [{ channel: "y", name: "calls" }, { field: "avg_ms", name: "avg ms" }, { field: "errors", name: "errors" }] }}
              />
            ) : (
              <Empty description={t("admin.stats.empty")} />
            )}
            <Source>agent_run_steps</Source>
          </Card>
        </Col>
        <Col xs={24} md={12} xl={8}>
          <Card size="small" title={t("admin.stats.ai.credits")} loading={loading && !data}>
            <Bar
              {...base}
              height={260}
              data={CREDIT_KINDS.map((k) => ({ kind: t(`admin.stats.ai.credits.${k}`), value: data?.credits?.[k] ?? 0 }))}
              xField="kind"
              yField="value"
              colorField="kind"
              legend={false}
              label={{ text: "value", position: "right", dx: 4 }}
              axis={{ x: { title: false }, y: false }}
            />
            <Source>credit_ledger</Source>
          </Card>
        </Col>
        <Col xs={24} xl={8}>
          <Card size="small" title={t("admin.stats.ai.tokens")} loading={loading && !data}>
            <Row gutter={[12, 12]}>
              <Col span={12}>
                <Statistic title={t("admin.stats.ai.tokens.in")} value={s?.tokens_in ?? 0} />
              </Col>
              <Col span={12}>
                <Statistic title={t("admin.stats.ai.tokens.out")} value={s?.tokens_out ?? 0} />
              </Col>
              <Col span={12}>
                <Statistic
                  title={t("admin.stats.ai.tokens.per_run")}
                  value={runs ? Math.round(((s?.tokens_in ?? 0) + (s?.tokens_out ?? 0)) / runs) : 0}
                />
              </Col>
              <Col span={12}>
                <Statistic
                  title={
                    <span>
                      {t("admin.stats.ai.improve")}{" "}
                      <Tooltip title={t("admin.stats.ai.improve.tip")}>
                        <InfoCircleOutlined />
                      </Tooltip>
                    </span>
                  }
                  value={pct(s?.improve ?? 0, runs)}
                  suffix="%"
                />
              </Col>
            </Row>
            <Typography.Title level={5} style={{ marginTop: 16 }}>
              {t("admin.stats.ai.clients")}
            </Typography.Title>
            {(data?.clients ?? []).map((c) => (
              <div key={c.client} style={{ display: "flex", gap: 8, alignItems: "center" }}>
                <span style={{ width: 64 }}>{c.client}</span>
                <Progress percent={Number(pct(c.runs ?? 0, runs))} size="small" style={{ flex: 1, margin: 0 }} />
              </div>
            ))}
            <Source>agent_runs</Source>
          </Card>
        </Col>
      </Row>

      <Row gutter={[16, 16]}>
        <Col xs={24}>
          <Card size="small" title={t("admin.stats.feedback.title")} loading={loading && !data}>
            <Row gutter={[12, 12]}>
              <Col span={8}>
                <Statistic title={t("admin.stats.feedback.up")} value={s?.feedback_up ?? 0} />
              </Col>
              <Col span={8}>
                <Statistic title={t("admin.stats.feedback.down")} value={s?.feedback_down ?? 0} />
              </Col>
              <Col span={8}>
                <Statistic
                  title={t("admin.stats.feedback.coverage")}
                  value={pct((s?.feedback_up ?? 0) + (s?.feedback_down ?? 0), runs)}
                  suffix="%"
                />
              </Col>
            </Row>
            {data?.feedback_reasons?.length ? (
              <Bar
                {...base}
                height={200}
                data={data.feedback_reasons.map((r) => ({ reason: t(`admin.stats.feedback.tag.${r.reason}`), count: r.count ?? 0 }))}
                xField="reason"
                yField="count"
                label={{ text: "count", position: "right", dx: 4 }}
                axis={{ x: { title: false }, y: false }}
                style={{ fill: "#ff7875", maxWidth: 24 }}
              />
            ) : (
              <Empty description={t("admin.stats.empty")} image={Empty.PRESENTED_IMAGE_SIMPLE} />
            )}
            <Source>agent_feedback</Source>
          </Card>
        </Col>
      </Row>

      <Card size="small">
        <Tabs
          items={[
            { key: "runs", label: t("admin.stats.runs.title"), children: <AgentRunTable /> },
            { key: "feedback", label: t("admin.stats.feedback.review"), children: <AgentFeedbackTable /> },
          ]}
        />
      </Card>
    </div>
  );
};

export default AgentStats;
