import { TCodeConvertor, XmlToReact } from "./utilities";

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
  return <>{jsx}</>;
};

export default Widget;
