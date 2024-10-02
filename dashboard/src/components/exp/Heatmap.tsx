import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { message, Select, Tooltip, Typography } from "antd";

import { get } from "../../request";
import { IUserOperationDailyResponse } from "../api/Exp";

interface IOptions {
  value: string;
  label: string;
}
interface IDailyData {
  date: string;
  commits: number;
  year: number;
  month: number;
  day: number;
  week: number;
}

interface IWidget {
  studioName?: string;
}
const HeatmapWidget = ({ studioName }: IWidget) => {
  const [dailyData, setDailyData] = useState<IDailyData[]>([]);
  const [year, setYear] = useState<IOptions[]>([]);

  const thisYear = new Date().getFullYear();
  const [currYear, setCurrYear] = useState<string>(thisYear.toString());
  const intl = useIntl();

  useEffect(() => {
    get<IUserOperationDailyResponse>(
      `/v2/user-operation-daily?view=user-year&studio_name=${studioName}&year=2021`
    ).then((json) => {
      if (json.ok) {
        //找到起止年份
        if (json.data.rows.length > 0) {
          const yearStart = new Date(json.data.rows[0].date_int).getFullYear();
          const yearEnd = new Date(
            json.data.rows[json.data.rows.length - 1].date_int
          ).getFullYear();
          let yearOption: IOptions[] = [];
          for (let index = yearStart; index <= yearEnd; index++) {
            yearOption.push({
              value: index.toString(),
              label: index.toString(),
            });
          }
          setYear(yearOption);
        }

        const data = json.data.rows.map((item) => {
          const date = new Date(item.date_int);
          const oneJan = new Date(date.getFullYear(), 0, 1);
          const week = Math.ceil(
            ((date.getTime() - oneJan.getTime()) / 86400000 +
              oneJan.getDay() +
              1) /
              7
          );

          return {
            date:
              date.getFullYear() +
              "-" +
              (date.getMonth() + 1) +
              "-" +
              date.getDate(),
            year: date.getFullYear(),
            month: date.getMonth() + 1,
            day: date.getDay(),
            week: week,
            commits: Math.floor(item.duration / 1000 / 60),
          };
        });
        console.log("data", data);
        setDailyData(data);
      } else {
        message.error(json.message);
      }
    });
  }, [studioName]);

  const yAxisLabel = new Array(7).fill(1).map((item, id) => {
    return (
      <div key={id} style={{ display: "inline-block", width: "5em" }}>
        <Typography.Text>
          {intl.formatMessage({ id: `labels.week.${id}` })}
        </Typography.Text>
      </div>
    );
  });
  const dayColor = ["#9be9a8", "#40c463", "#30a14e", "#216e39"];
  const weeks = new Array(54).fill(1);
  const heatmap = weeks.map((item, week) => {
    const days = new Array(7).fill(1);
    return (
      <div key={week}>
        {days.map((itemDay, day) => {
          const currDate = dailyData.find(
            (value) =>
              value.year === parseInt(currYear) &&
              value.week === week &&
              value.day === day
          );
          const time = currDate?.commits;
          const color = time
            ? time > 100
              ? dayColor[3]
              : time > 50
              ? dayColor[2]
              : time > 20
              ? dayColor[1]
              : time > 0
              ? dayColor[0]
              : "rgba(0,0,0,0)"
            : "rgba(0,0,0,0)";
          return (
            <div
              key={day}
              style={{
                display: "inline-block",
                width: 12,
                height: 12,
                backgroundColor: `rgba(128,128,128,0.2)`,
                margin: 0,
                borderRadius: 2,
                outline: "1px solid gray",
              }}
            >
              <Tooltip
                placement="top"
                title={`${currDate?.date}/${currDate?.commits}分钟`}
              >
                <div
                  style={{
                    width: 12,
                    height: 12,
                    backgroundColor: color,
                  }}
                ></div>
              </Tooltip>
            </div>
          );
        })}
      </div>
    );
  });

  return (
    <div style={{ width: 1000 }}>
      <div style={{ textAlign: "right" }} key="toolbar">
        <Select
          defaultValue={thisYear.toString()}
          style={{ width: 120 }}
          onChange={(value: string) => {
            console.log(`selected ${value}`);
            setCurrYear(value);
          }}
          options={year}
        />
      </div>
      <div style={{ display: "flex" }} key="map">
        <div style={{ width: "5em" }} key="label">
          {yAxisLabel}
        </div>
        {heatmap}
      </div>
    </div>
  );
};

export default HeatmapWidget;
