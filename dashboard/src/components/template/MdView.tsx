import { Typography } from "antd";
import { TCodeConvertor, XmlToReact } from "./utilities";
import { gfwClear } from "../../gfwlist";
const { Paragraph, Text } = Typography;

interface IWidget {
  html?: string | null;
  className?: string;
  placeholder?: string;
  wordWidget?: boolean;
  convertor?: TCodeConvertor;
  style?: React.CSSProperties;
}
const Widget = ({
  html,
  className,
  wordWidget = false,
  placeholder,
  convertor,
  style,
}: IWidget) => {
  if (html && html.trim() !== "") {
    return (
      <Paragraph>{XmlToReact(gfwClear(html), wordWidget, convertor)}</Paragraph>
    );
  } else {
    return <Text type="secondary">{placeholder}</Text>;
  }
};

export default Widget;
