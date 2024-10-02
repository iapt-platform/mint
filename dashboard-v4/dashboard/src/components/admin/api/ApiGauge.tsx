import { useEffect, useRef, useState } from "react";
import { Gauge } from "@ant-design/plots";
import { get } from "../../../request";
import { StatisticCard } from "@ant-design/pro-components";

interface IApiResponse {
  ok: boolean;
  message: string;
  data: number;
}
const ApiGaugeWidget = () => {
  const min = 0;
  const max = 1;
  const [percent, setPercent] = useState<number>(0);
  const [delay, setDelay] = useState<number>(0);
  const maxAxis = 5000; //最大量程-毫秒

  useEffect(() => {
    let timer = setInterval(() => {
      get<IApiResponse>("/v2/api/10?item=average").then((json) => {
        setPercent(json.data / maxAxis);
        setDelay(json.data);
      });
    }, 1000 * 5);
    return () => {
      clearInterval(timer);
    };
  }, []);

  const graphRef: any = useRef(null);

  const config = {
    percent: percent,
    range: {
      ticks: [min, max],
      color: ["l(0) 0:#30BF78 0.5:#FAAD14 1:#F4664A"],
    },
    indicator: {
      pointer: {
        style: {
          stroke: "#D0D0D0",
        },
      },
      pin: {
        style: {
          stroke: "#D0D0D0",
        },
      },
    },
    axis: {
      label: {
        formatter(v: any) {
          return Number(v) * maxAxis;
        },
      },
      subTickLine: {
        count: 3,
      },
    },
  };

  return (
    <StatisticCard
      style={{ width: 400 }}
      statistic={{
        title: "平均相应时间",
        value: delay,
        suffix: "/ ms",
      }}
      chart={
        <Gauge
          ref={graphRef}
          {...config}
          onReady={(chart) => {
            graphRef.current = chart;
          }}
        />
      }
    />
  );
};

export default ApiGaugeWidget;
