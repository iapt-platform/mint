import { StatisticCard } from "@ant-design/pro-components";
import { useState } from "react";
import RcResizeObserver from "rc-resize-observer";
import ApiGauge from "../../../components/admin/api/ApiGauge";
import ApiDelayHour from "../../../components/admin/api/ApiDelayHour";
const { Divider } = StatisticCard;
const Widget = () => {
  const [responsive, setResponsive] = useState(true);

  return (
    <RcResizeObserver
      key="resize-observer"
      onResize={(offset) => {
        setResponsive(offset.width < 596);
      }}
    >
      <StatisticCard.Group
        direction={responsive ? "column" : "row"}
        title="总量"
      >
        <ApiDelayHour type="average" title={"平均相应时间"} />
        <Divider type={responsive ? "horizontal" : "vertical"} />
        <ApiDelayHour type="count" title={"请求次数"} />
        <Divider type={responsive ? "horizontal" : "vertical"} />
        <ApiGauge />
      </StatisticCard.Group>
    </RcResizeObserver>
  );
};

export default Widget;
