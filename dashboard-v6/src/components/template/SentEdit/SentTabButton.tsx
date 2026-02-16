import { Badge, Space } from "antd";

interface IWidget {
  style?: React.CSSProperties;
  icon?: JSX.Element;
  type: string;
  sentId: string;
  count?: number;
  title?: string;
}
const SentTabButtonWidget = ({
  style,
  icon,
  type,
  sentId,
  title,
  count = 0,
}: IWidget) => {
  return (
    <Space>
      <>{title}</>
      <Badge size="small" color="geekblue" count={count}></Badge>
    </Space>
  );
};

export default SentTabButtonWidget;
