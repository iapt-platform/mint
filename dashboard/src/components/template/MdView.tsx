import { Typography } from "antd";
import { TCodeConvertor, XmlToReact } from "./utilities";
const { Text } = Typography;

interface IWidget {
  html?: string;
  placeholder?: string;
  wordWidget?: boolean;
  convertor?: TCodeConvertor;
}
const Widget = ({
  html,
  wordWidget = false,
  placeholder,
  convertor,
}: IWidget) => {
  const jsx = html ? XmlToReact(html, wordWidget, convertor) : placeholder;
  return <Text>{jsx}</Text>;
};

export default Widget;
