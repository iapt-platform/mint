import { Typography } from "antd";
import { marked } from "marked";

const { Text } = Typography;

interface IWidget {
  text?: string;
  className?: string;
}
const MarkedWidget = ({ text, className }: IWidget) => {
  return (
    <Text className={className}>
      <div
        className={className}
        dangerouslySetInnerHTML={{
          __html: marked.parse(text ? text : ""),
        }}
      />
    </Text>
  );
};

export default MarkedWidget;
