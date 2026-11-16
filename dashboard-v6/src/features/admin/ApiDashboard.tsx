import { Grid } from "antd";
import { StatisticCard } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import ApiStatCard from "../../components/admin/ApiStatCard";
import ApiGaugeCard from "../../components/admin/ApiGaugeCard";
import { useApiAverage, useApiStat } from "../../hooks/useAdmin";

/** API 监控：平均响应时间、请求次数（按时段）+ 实时平均响应时间仪表盘。 */
const ApiDashboard = () => {
  const intl = useIntl();
  const screens = Grid.useBreakpoint();
  // 窄屏竖排（v4 用 ResizeObserver 判断 596px，这里用 antd 断点）
  const vertical = !screens.md;
  const average = useApiStat("average");
  const count = useApiStat("count");
  const delay = useApiAverage();

  return (
    <StatisticCard.Group
      direction={vertical ? "column" : "row"}
      title={intl.formatMessage({ id: "admin.api.total" })}
    >
      <ApiStatCard title={intl.formatMessage({ id: "admin.api.average" })} data={average} />
      <StatisticCard.Divider orientation={vertical ? "horizontal" : "vertical"} />
      <ApiStatCard title={intl.formatMessage({ id: "admin.api.count" })} data={count} suffix="" />
      <StatisticCard.Divider orientation={vertical ? "horizontal" : "vertical"} />
      <ApiGaugeCard title={intl.formatMessage({ id: "admin.api.average" })} delay={delay} />
    </StatisticCard.Group>
  );
};

export default ApiDashboard;
