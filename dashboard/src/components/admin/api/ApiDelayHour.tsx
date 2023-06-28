import { useEffect, useState } from "react";
import { Column } from "@ant-design/plots";
import { put } from "../../../request";
import { StatisticCard } from "@ant-design/pro-components";

interface IApiDelay {
  date: string;
  value: number;
}
interface IApiDelayResponse {
  ok: boolean;
  message: string;
  data: IApiDelay[];
}
interface IApiRequest {
  api: string;
  item: string;
}

interface IWidget {
  type: "average" | "count" | "delay";
  api?: string;
}

const ApiDelayHourWidget = ({ type, api = "all" }: IWidget) => {
  const [delayData, setDelayData] = useState<IApiDelay[]>([]);

  useEffect(() => {
    put<IApiRequest, IApiDelayResponse>("/v2/api/10", {
      api: api,
      item: type,
    }).then((json) => {
      console.log("data", json.data);
      setDelayData(json.data);
    });
  }, []);

  const config = {
    data: delayData,
    xField: "date",
    yField: "value",
    seriesField: "",
    xAxis: {
      label: {
        autoHide: true,
        autoRotate: false,
      },
    },
  };

  return (
    <StatisticCard
      style={{ width: 400 }}
      statistic={{
        title: "平均响应时间",
        value: "",
        suffix: "/ ms",
      }}
      chart={<Column {...config} />}
    />
  );
};

export default ApiDelayHourWidget;
