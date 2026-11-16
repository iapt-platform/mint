import { Gauge } from "@ant-design/plots";
import { StatisticCard } from "@ant-design/pro-components";
import type { ReactNode } from "react";

interface IWidget {
  title?: ReactNode;
  /** 当前平均响应时间（毫秒） */
  delay: number;
  /** 量程上限（毫秒） */
  max?: number;
}

/** 平均响应时间仪表盘：绿 → 黄 → 红。 */
const ApiGaugeCard = ({ title, delay, max = 5000 }: IWidget) => (
  <StatisticCard
    style={{ maxWidth: 400 }}
    statistic={{ title, value: delay, suffix: "/ ms" }}
    chart={
      <Gauge
        height={260}
        data={{ target: Math.min(delay, max), total: max, thresholds: [max * 0.3, max * 0.6, max] }}
        scale={{ color: { range: ["#30BF78", "#FAAD14", "#F4664A"] } }}
        legend={false}
      />
    }
  />
);

export default ApiGaugeCard;
