import { Tag } from "antd";

interface IQaCtl {
  value?: string;
}
const ConfidenceCtl = ({ value = "0%" }: IQaCtl) => {
  const confidence = parseFloat(value.replace("%", "")); // 结果为 90
  return <Tag color={confidence < 90 ? "red" : "green"}>{value}</Tag>;
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IQaCtl;
  console.log(prop);
  return (
    <>
      <ConfidenceCtl {...prop} />
    </>
  );
};

export default Widget;
