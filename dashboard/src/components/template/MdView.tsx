import { Typography } from "antd";
import { TCodeConvertor, XmlToReact } from "./utilities";
const { Paragraph } = Typography;

interface IWidget {
  html?: string;
  className?: string;
  placeholder?: string;
  wordWidget?: boolean;
  convertor?: TCodeConvertor;
}
const Widget = ({
  html,
  className,
  wordWidget = false,
  placeholder,
  convertor,
}: IWidget) => {
  const jsx = html ? XmlToReact(html, wordWidget, convertor) : placeholder;
  return <Paragraph className={className}>{jsx}</Paragraph>;
};

export default Widget;
