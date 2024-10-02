import { DualAxes } from "@ant-design/plots";
import { message } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import {
  IUserOperationDailyRequest,
  IUserOperationDailyResponse,
} from "../api/Exp";

interface IDailyData {
  time: string;
  sum: number;
  hit: number;
}

interface IWidget {
  studioName?: string;
}
const StudyTimeDualAxesWidget = ({ studioName }: IWidget) => {
  const [dailyData, setDailyData] = useState<IDailyData[]>([]);

  useEffect(() => {
    get<IUserOperationDailyResponse>(
      `/v2/user-operation-daily?view=user-all&studio_name=${studioName}`
    ).then((json) => {
      if (json.ok) {
        if (json.data.count === 0) {
          return;
        }
        let timeSum = 0;
        /**
         * 算法
         * 返回的数据只包涵有活动的天。需要补足缺失的日子。
         * 找到最小日期
         * 查询从最小时期到现在的每天的数值
         */
        const today = new Date();
        let fullData: IUserOperationDailyRequest[] = [];
        for (
          let day = json.data.rows[0].date_int;
          day < today.getTime();
          day += 24 * 3600 * 1000
        ) {
          const currDay = json.data.rows.find(
            (value) =>
              new Date(value.date_int).toLocaleDateString() ===
              new Date(day).toLocaleDateString()
          );
          if (currDay) {
            fullData.push(currDay);
          } else {
            fullData.push({
              date_int: day,
              duration: 0,
              hit: 0,
            });
          }
        }
        const data = fullData.map((item) => {
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

export default StudyTimeDualAxesWidget;
