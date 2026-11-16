import { Column } from "@ant-design/plots";
import { StatisticCard } from "@ant-design/pro-components";
import type { ReactNode } from "react";
import type { IApiStatPoint } from "../../api/admin";

interface IWidget {
  title?: ReactNode;
  data: IApiStatPoint[];
  suffix?: string;
}

/** 接口统计序列：标题 + 柱状图（API 监控页）。 */
const ApiStatCard = ({ title, data, suffix = "/ ms" }: IWidget) => (
  <StatisticCard
    statistic={{ title, value: "", suffix }}
    chart={
      <Column
        data={data}
        xField="date"
        yField="value"
        height={300}
        axis={{ x: { labelAutoHide: true, labelAutoRotate: false } }}
      />
    }
  />
);

export default ApiStatCard;
