// src/features/admin-stats/ReadingStats.tsx
/**
 * 管理后台「阅读统计」：指标卡 → 趋势 + 热门章节 → 分布与读经积分。
 * 数据来自 GET /v3/admin/reading-stats，口径写在每张卡片的来源脚注里。
 */
import { useState } from "react";
import { Button, Card, Col, Empty, Result, Row, Statistic, Tooltip, Typography } from "antd";
import { InfoCircleOutlined } from "@ant-design/icons";
import { Column, Line, Pie, Tiny } from "@ant-design/plots";
import KpiCard from "../../components/stats/KpiCard";
import { useReadingStats } from "../../hooks/useAdminStats";
import { change, pct, tiny, useChartTheme, useStatsRange, useT } from "./helpers";
import { Source, StatsToolbar } from "./shared";

type Client = "all" | "web" | "mobile";
type TrendTab = "readers" | "minutes" | "lookups";

const ReadingStats = () => {
  const t = useT();
  const chartTheme = useChartTheme();
  const { range, setRange, query } = useStatsRange();
  const [client, setClient] = useState<Client>("all");
  const [tab, setTab] = useState<TrendTab>("readers");
  const { data, loading, error, refresh } = useReadingStats({ ...query, client });

  const base = { theme: chartTheme, autoFit: true } as const;
  const s = data?.summary;
  const p = data?.previous;
  const daily = data?.daily ?? [];
  const tagLabel = (tag?: string) => t(`admin.stats.tag.${tag ?? "other"}`);

  const toolbar = (
    <StatsToolbar<Client>
      title={t("admin.stats.reading.title")}
      segments={(["all", "web", "mobile"] as const).map((v) => ({ value: v, label: t(`admin.stats.client.${v}`) }))}
      segment={client}
      onSegment={setClient}
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

  const readers = s?.active_readers ?? 0;
  const readingUsers = s?.reading_users ?? 0;
  const trendRows =
    tab === "readers"
      ? daily.flatMap((d) => [
          { date: d.date?.slice(5), value: d.readers_web ?? 0, series: t("admin.stats.client.web") },
          { date: d.date?.slice(5), value: d.readers_mobile ?? 0, series: t("admin.stats.client.mobile") },
        ])
      : daily.map((d) => ({
          date: d.date?.slice(5),
          value: tab === "minutes" ? (d.reading_minutes ?? 0) : (d.dict_lookups ?? 0),
          series: t(`admin.stats.reading.trend.${tab}`),
        }));

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
      {toolbar}

      <Row gutter={[16, 16]}>
        <Col xs={24} sm={12} xl={6}>
          <KpiCard
            loading={loading && !data}
            title={t("admin.stats.reading.readers")}
            tip={t("admin.stats.reading.readers.tip")}
            value={readers}
            change={change(s?.active_readers, p?.active_readers)}
            changeLabel={t("admin.stats.compare")}
            chart={
              <Tiny.Area
                {...base}
                height={46}
                data={tiny(daily.map((d) => (d.readers_web ?? 0) + (d.readers_mobile ?? 0)))}
                xField="index"
                yField="value"
                shapeField="smooth"
                style={{ fillOpacity: 0.3 }}
              />
            }
            footer={
              <>
                <span>{t("admin.stats.reading.dau", { dau: data?.sticky?.dau ?? 0 })}</span>
                <span>
                  {t("admin.stats.reading.sticky", {
                    ratio: pct(data?.sticky?.dau ?? 0, data?.sticky?.mau ?? 0),
                  })}
                </span>
              </>
            }
            source="recents"
          />
        </Col>
        <Col xs={24} sm={12} xl={6}>
          <KpiCard
            loading={loading && !data}
            title={t("admin.stats.reading.time")}
            tip={t("admin.stats.reading.time.tip")}
            value={s?.reading_seconds == null ? null : s.reading_seconds / 3600}
            precision={1}
            suffix={t("admin.stats.reading.time.unit")}
            change={change(s?.reading_seconds, p?.reading_seconds)}
            changeLabel={t("admin.stats.compare")}
            chart={
              s?.reading_seconds == null ? undefined : (
                <Tiny.Column
                  {...base}
                  height={46}
                  data={tiny(daily.map((d) => d.reading_minutes ?? 0))}
                  xField="index"
                  yField="value"
                />
              )
            }
            footer={
              s?.reading_seconds == null ? (
                <span>{t("admin.stats.na")}</span>
              ) : (
                <span>
                  {t("admin.stats.reading.time.avg", {
                    users: readingUsers,
                    minutes: readingUsers ? Math.round(s.reading_seconds / 60 / readingUsers) : 0,
                  })}
                </span>
              )
            }
            source="reading_dailies"
          />
        </Col>
        <Col xs={24} sm={12} xl={6}>
          <KpiCard
            loading={loading && !data}
            title={t("admin.stats.reading.chapters")}
            tip={t("admin.stats.reading.chapters.tip")}
            value={s?.chapters_opened ?? 0}
            change={change(s?.chapters_opened, p?.chapters_opened)}
            changeLabel={t("admin.stats.compare")}
            footer={
              <>
                <span>
                  {t("admin.stats.reading.chapters.avg", {
                    avg: readers ? ((s?.chapters_opened ?? 0) / readers).toFixed(1) : "0",
                  })}
                </span>
                {s?.page_views != null ? (
                  <span>{t("admin.stats.reading.views", { views: s.page_views.toLocaleString() })}</span>
                ) : null}
              </>
            }
            source="recents · views"
          />
        </Col>
        <Col xs={24} sm={12} xl={6}>
          <KpiCard
            loading={loading && !data}
            title={t("admin.stats.reading.lookups")}
            tip={t("admin.stats.reading.lookups.tip")}
            value={s?.dict_lookups ?? 0}
            change={change(s?.dict_lookups, p?.dict_lookups)}
            changeLabel={t("admin.stats.compare")}
            chart={
              <Tiny.Line
                {...base}
                height={46}
                data={tiny(daily.map((d) => d.dict_lookups ?? 0))}
                xField="index"
                yField="value"
                shapeField="smooth"
              />
            }
            source="user_operation_logs"
          />
        </Col>
      </Row>

      <Row gutter={[16, 16]}>
        <Col xs={24} xl={16}>
          <Card
            size="small"
            loading={loading && !data}
            tabList={(["readers", "minutes", "lookups"] as const).map((k) => ({
              key: k,
              label: t(`admin.stats.reading.trend.${k}`),
            }))}
            activeTabKey={tab}
            onTabChange={(k) => setTab(k as TrendTab)}
          >
            {tab === "minutes" ? (
              <Column {...base} height={300} data={trendRows} xField="date" yField="value" axis={{ x: { labelAutoHide: true } }} />
            ) : (
              <Line
                {...base}
                height={300}
                data={trendRows}
                xField="date"
                yField="value"
                colorField="series"
                shapeField="smooth"
                axis={{ x: { labelAutoHide: true } }}
              />
            )}
            <Source>
              {tab === "readers" ? "recents" : tab === "minutes" ? "reading_dailies" : "user_operation_logs"}
            </Source>
          </Card>
        </Col>
        <Col xs={24} xl={8}>
          <Card size="small" title={t("admin.stats.reading.top")} loading={loading && !data}>
            {data?.top_chapters?.length ? (
              data.top_chapters.map((c, i) => (
                <div
                  key={c.article_id}
                  style={{ display: "flex", alignItems: "center", gap: 10, padding: "6px 0", minWidth: 0 }}
                >
                  <span
                    style={{
                      width: 20,
                      height: 20,
                      borderRadius: 10,
                      flex: "none",
                      fontSize: 12,
                      display: "inline-flex",
                      alignItems: "center",
                      justifyContent: "center",
                      background: i < 3 ? "#1677ff" : "rgba(127,127,127,.15)",
                      color: i < 3 ? "#fff" : undefined,
                    }}
                  >
                    {i + 1}
                  </span>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <Typography.Text ellipsis style={{ display: "block" }}>
                      {c.title}
                    </Typography.Text>
                    <Typography.Text type="secondary" ellipsis style={{ display: "block", fontSize: 12 }}>
                      {c.collection ?? c.article_id}
                    </Typography.Text>
                  </div>
                  <span style={{ fontVariantNumeric: "tabular-nums" }}>
                    {t("admin.stats.reading.top.readers", { n: c.readers ?? 0 })}
                  </span>
                </div>
              ))
            ) : (
              <Empty description={t("admin.stats.empty")} />
            )}
            <Source>recents</Source>
          </Card>
        </Col>
      </Row>

      <Row gutter={[16, 16]}>
        {(["pitaka_share", "layer_share"] as const).map((key) => (
          <Col xs={24} md={12} xl={8} key={key}>
            <Card
              size="small"
              title={t(key === "pitaka_share" ? "admin.stats.reading.pitaka" : "admin.stats.reading.layer")}
              loading={loading && !data}
            >
              {data?.[key]?.length ? (
                <Pie
                  {...base}
                  height={240}
                  data={data[key].map((r) => ({ type: tagLabel(r.tag), value: r.records ?? 0 }))}
                  angleField="value"
                  colorField="type"
                  innerRadius={0.6}
                  label={{ text: "type", position: "outside" }}
                  legend={false}
                />
              ) : (
                <Empty description={t("admin.stats.empty")} />
              )}
              <Source>recents · tag_maps</Source>
            </Card>
          </Col>
        ))}
        <Col xs={24} xl={8}>
          <Card size="small" title={t("admin.stats.reading.duration")} loading={loading && !data}>
            {data?.duration_buckets?.length ? (
              <Column
                {...base}
                height={160}
                data={data.duration_buckets}
                xField="bucket"
                yField="user_days"
                label={{ text: "user_days", position: "inside" }}
              />
            ) : (
              <Empty description={t("admin.stats.na")} />
            )}
            <Typography.Title level={5} style={{ marginTop: 12 }}>
              {t("admin.stats.reading.credits")}
            </Typography.Title>
            {data?.reading_credits ? (
              <Row gutter={[12, 12]}>
                <Col span={12}>
                  <Statistic title={t("admin.stats.reading.credits.total")} value={data.reading_credits.credits} />
                </Col>
                <Col span={12}>
                  <Statistic title={t("admin.stats.reading.credits.users")} value={data.reading_credits.users} />
                </Col>
                <Col span={12}>
                  <Statistic
                    title={t("admin.stats.reading.credits.capped")}
                    value={data.reading_credits.capped_user_days}
                  />
                </Col>
                <Col span={12}>
                  <Statistic
                    title={
                      <span>
                        {t("admin.stats.reading.credits.clamped")}{" "}
                        <Tooltip title={t("admin.stats.reading.credits.clamped.tip")}>
                          <InfoCircleOutlined />
                        </Tooltip>
                      </span>
                    }
                    value={((data.reading_credits.clamped_ratio ?? 0) * 100).toFixed(1)}
                    suffix="%"
                  />
                </Col>
              </Row>
            ) : (
              <Typography.Text type="secondary">{t("admin.stats.na")}</Typography.Text>
            )}
            <div>
              <Source>reading_dailies · credit_ledger</Source>
            </div>
          </Card>
        </Col>
      </Row>
    </div>
  );
};

export default ReadingStats;
