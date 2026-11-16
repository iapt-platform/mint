// src/components/stats/KpiCard.tsx
/**
 * 统计看板的指标卡：标题（可带说明）、主数字、迷你图、与上一同长区间的对比、底部补充、数据来源。
 *
 * 纯 UI：只吃 props，不知道是什么业务。基于 pro-components 的 StatisticCard。
 * `change` 是相对上一区间的变化比例（0.084 = +8.4%），null 表示没有可比数据；
 * `inverse` 为 true 时下降算好事（耗时、出错率），颜色反过来。
 */
import type { ReactNode } from "react";
import { StatisticCard } from "@ant-design/pro-components";
import { Typography } from "antd";
import { CaretDownOutlined, CaretUpOutlined } from "@ant-design/icons";

export interface KpiCardProps {
  title: ReactNode;
  tip?: string;
  value: number | string | null;
  suffix?: ReactNode;
  precision?: number;
  change?: number | null;
  inverse?: boolean;
  changeLabel?: ReactNode;
  chart?: ReactNode;
  footer?: ReactNode;
  source?: ReactNode;
  loading?: boolean;
}

const KpiCard = ({
  title,
  tip,
  value,
  suffix,
  precision,
  change,
  inverse,
  changeLabel,
  chart,
  footer,
  source,
  loading,
}: KpiCardProps) => {
  const good = change == null ? undefined : inverse ? change <= 0 : change >= 0;
  return (
    <StatisticCard
      loading={loading}
      title={title}
      tooltip={tip}
      statistic={{
        value: value ?? "—",
        suffix: value == null ? undefined : suffix,
        precision,
        description:
          change == null ? undefined : (
            <span style={{ fontSize: 12 }}>
              {changeLabel}{" "}
              <span style={{ color: good ? "#52c41a" : "#ff4d4f", fontVariantNumeric: "tabular-nums" }}>
                {change >= 0 ? <CaretUpOutlined /> : <CaretDownOutlined />} {Math.abs(change * 100).toFixed(1)}%
              </span>
            </span>
          ),
      }}
      chart={chart ? <div style={{ height: 46 }}>{chart}</div> : undefined}
      footer={
        <div style={{ display: "flex", flexDirection: "column", gap: 4 }}>
          {footer ? (
            <div style={{ display: "flex", flexWrap: "wrap", gap: 12, fontSize: 12 }}>{footer}</div>
          ) : null}
          {source ? (
            <Typography.Text type="secondary" style={{ fontSize: 12 }}>
              {source}
            </Typography.Text>
          ) : null}
        </div>
      }
    />
  );
};

export default KpiCard;
