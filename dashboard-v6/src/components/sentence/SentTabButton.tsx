import { Badge, Space } from "antd";
import type { JSX } from "react";

interface IWidget {
  style?: React.CSSProperties;
  icon?: JSX.Element;
  type: string;
  sentId: string;
  count?: number;
  title?: string;
}
const SentTabButtonWidget = ({ title, count = 0 }: IWidget) => {
  return (
    <Space>
      <>{title}</>
      <Badge size="small" color="geekblue" count={count}></Badge>
    </Space>
  );
};

export default SentTabButtonWidget;
