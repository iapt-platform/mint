import { Typography } from "antd";
import { marked } from "marked";

const { Text } = Typography;

interface IWidget {
  text?: string;
  style?: React.CSSProperties;
  className?: string;
}
const MarkedWidget = ({ text, style, className }: IWidget) => {
  return (
    <Text className={className}>
      <div
        style={style}
        className={className}
        dangerouslySetInnerHTML={{
          __html: marked.parse(text ? text : ""),
        }}
      />
    </Text>
  );
};

export default MarkedWidget;
