import { Typography } from "antd";
import { TCodeConvertor, XmlToReact } from "./utilities";
const { Text } = Typography;

interface IWidget {
  html?: string;
  wordWidget?: boolean;
  convertor?: TCodeConvertor;
}
const Widget = ({
  html = "<div></div>",
  wordWidget = false,
  convertor,
}: IWidget) => {
  const jsx = XmlToReact(html, wordWidget, convertor);
  return <Text>{jsx}</Text>;
};

export default Widget;
