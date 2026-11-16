// src/features/admin-stats/helpers.ts
// 两个统计页共用的 hook 与纯函数：区间、文案、图表主题、对比计算、迷你图数据。
import { useMemo, useState, type ReactNode } from "react";
import { useIntl } from "react-intl";
import { theme } from "antd";
import dayjs, { type Dayjs } from "dayjs";
import type { AgentRoute } from "../../api/adminStats";
export type Range = [Dayjs, Dayjs];

/** 统计接口以 UTC 日为准。 */
/**
 * 全部研究路径，按画图 / 筛选的固定顺序。类型来自 OpenAPI（mint AgentRun::ROUTES）：
 * 规格里多了一种路由而这里没加，下面的 ALL_ROUTES_LISTED 编译报错。
 */
export const ROUTES = ["question", "term", "passage", "chat"] as const satisfies readonly AgentRoute[];
const ALL_ROUTES_LISTED: Exclude<AgentRoute, (typeof ROUTES)[number]> extends never ? true : never = true;
void ALL_ROUTES_LISTED;

export const today = () => dayjs().startOf("day");

export const useStatsRange = (days = 30) => {
  const [range, setRange] = useState<Range>(() => [today().subtract(days - 1, "day"), today()]);
  const query = useMemo(
    () => ({ from: range[0].format("YYYY-MM-DD"), to: range[1].format("YYYY-MM-DD") }),
    [range],
  );
  return { range, setRange, query };
};

/** 文案简写：t("admin.stats.xxx", { n: 1 }) */
export const useT = () => {
  const intl = useIntl();
  return (id: string, values?: Record<string, ReactNode>) =>
    intl.formatMessage({ id }, values as Record<string, string>) as string;
};

/** 图表主题跟着 antd 的明暗走：看当前容器背景色的亮度。 */
export const useChartTheme = (): "classic" | "classicDark" => {
  const { token } = theme.useToken();
  const hex = token.colorBgContainer.replace("#", "");
  const [r, g, b] = [0, 2, 4].map((i) => parseInt(hex.slice(i, i + 2), 16) || 0);
  return 0.299 * r + 0.587 * g + 0.114 * b < 128 ? "classicDark" : "classic";
};

/** 相对上一区间的变化比例；上一区间是 0 或缺数据时没有可比性。 */
export const change = (cur?: number | null, prev?: number | null): number | null =>
  cur == null || prev == null || prev === 0 ? null : (cur - prev) / prev;

export const pct = (part: number, total: number): string =>
  total ? ((part / total) * 100).toFixed(1) : "0.0";

/** 迷你图数据：plots 2.x 的 Tiny 图要 {index, value} 加 xField / yField。 */
export const tiny = (values: number[]) => values.map((value, index) => ({ index, value }));

