import { Typography } from "antd";
import { marked } from "marked";

const { Text } = Typography;

interface IWidget {
  text?: string;
}
const MarkedWidget = ({ text }: IWidget) => {
  return (
    <Text>
      <div
        dangerouslySetInnerHTML={{
          __html: marked.parse(text ? text : ""),
        }}
      />
    </Text>
  );
};

export default MarkedWidget;
