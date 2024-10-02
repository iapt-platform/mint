import { Pie } from "@ant-design/plots";

export interface IPieData {
  type: string;
  value: number;
}
interface IWidget {
  data?: IPieData[];
}
const ExpPieWidget = ({ data = [] }: IWidget) => {
  console.log("pie data", data);
  const config = {
    appendPadding: 10,
    data,
    angleField: "value",
    colorField: "type",
    radius: 1,
    innerRadius: 0.6,
    label: {
      type: "inner",
      offset: "-50%",
      content: "{value}",
      style: {
        textAlign: "center",
        fontSize: 14,
        display: "none",
      },
    },
    interactions: [
      {
        type: "element-selected",
      },
      {
        type: "element-active",
      },
    ],
    statistic: {
      content: {
        style: {
          whiteSpace: "pre-wrap",
          overflow: "hidden",
          textOverflow: "ellipsis",
          display: "none",
        },
        content: "a",
      },
    },
  };
  return <Pie {...config} style={{ height: 120 }} />;
};

export default ExpPieWidget;
