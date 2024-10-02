import { Typography } from "antd";
import { TCodeConvertor, XmlToReact } from "./utilities";
import { gfwClear } from "../../gfwlist";
const { Text } = Typography;

interface IWidget {
  html?: string | null;
  className?: string;
  placeholder?: string;
  wordWidget?: boolean;
  convertor?: TCodeConvertor;
  style?: React.CSSProperties;
}
const MdViewWidget = ({
  html,
  className,
  wordWidget = false,
  placeholder,
  convertor,
  style,
}: IWidget) => {
  if (html && html.trim() !== "") {
    return (
      <Text className={className} style={style}>
        {XmlToReact(gfwClear(html), wordWidget, convertor)}
      </Text>
    );
  } else {
    return <Text type="secondary">{placeholder}</Text>;
  }
};

export default MdViewWidget;
