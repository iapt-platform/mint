// src/features/admin-stats/shared.tsx
// 两个统计页共用的小组件：区间 / 维度工具栏、来源脚注。
import type { ReactNode } from "react";
import { DatePicker, Segmented, Space, Typography } from "antd";
import { today, useT, type Range } from "./helpers";

export const Source = ({ children }: { children: ReactNode }) => {
  const t = useT();
  return (
    <Typography.Text type="secondary" style={{ fontSize: 12 }}>
      {t("admin.stats.source", { source: children })}
    </Typography.Text>
  );
};

export const StatsToolbar = <T extends string>({
  title,
  segments,
  segment,
  onSegment,
  range,
  onRange,
}: {
  title: ReactNode;
  segments: { value: T; label: ReactNode }[];
  segment: T;
  onSegment: (v: T) => void;
  range: Range;
  onRange: (r: Range) => void;
}) => {
  const t = useT();
  return (
    <div style={{ display: "flex", flexWrap: "wrap", alignItems: "center", gap: 12 }}>
      <Typography.Title level={4} style={{ margin: 0, marginRight: "auto" }}>
        {title}
      </Typography.Title>
      <Space wrap>
        <Segmented<T> options={segments} value={segment} onChange={onSegment} />
        <DatePicker.RangePicker
          value={range}
          allowClear={false}
          disabledDate={(d) => d.isAfter(today())}
          presets={[7, 30, 90].map((n) => ({
            label: t(`admin.stats.range.${n}`),
            value: [today().subtract(n - 1, "day"), today()] as Range,
          }))}
          onChange={(v) => v && v[0] && v[1] && onRange([v[0], v[1]])}
        />
      </Space>
    </div>
  );
};
