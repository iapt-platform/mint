import { DualAxes } from "@ant-design/plots";
import { message } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import { IUserOperationDailyResponse } from "../api/Exp";

interface IDailyData {
  time: string;
  sum: number;
  hit: number;
}

interface IWidget {
  studioName?: string;
}
const Widget = ({ studioName }: IWidget) => {
  const [dailyData, setDailyData] = useState<IDailyData[]>([]);

  useEffect(() => {
    get<IUserOperationDailyResponse>(
      `/v2/user-operation-daily?view=user-all&studio_name=${studioName}`
    ).then((json) => {
      if (json.ok) {
        let timeSum = 0;
        const data = json.data.rows.map((item) => {
          const date = new Date(item.date_int);
          timeSum += item.duration / 1000 / 3600;
          return {
            time:
              date.getFullYear() +
              "-" +
              (date.getMonth() + 1) +
              "-" +
              date.getDate(),
            sum: timeSum,
            hit: item.hit ? item.hit : 0,
          };
        });
        console.log("data", data);
        setDailyData(data);
      } else {
        message.error(json.message);
      }
    });
  }, [studioName]);

  const config = {
    data: [dailyData, dailyData],
    xField: "time",
    yField: ["hit", "sum"],
    limitInPlot: false,
    padding: [10, 20, 80, 30],
    // 需要设置底部 padding 值，同 css
    slider: {},
    meta: {
      time: {
        sync: false, // 开启之后 slider 无法重绘
      },
    },
    geometryOptions: [
      {
        geometry: "column",
      },
      {
        geometry: "line",
      },
    ],
  };
  return <DualAxes {...config} />;
};

export default Widget;
