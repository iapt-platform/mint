import { Typography } from "antd";
import { TCodeConvertor, XmlToReact } from "./utilities";
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
  const jsx =
    html && html.trim() !== "" ? (
      XmlToReact(html, wordWidget, convertor)
    ) : (
      <Text type="secondary">{placeholder}</Text>
    );
  return (
    <Paragraph style={style} className={className}>
      {jsx}
    </Paragraph>
  );
};

export default Widget;
