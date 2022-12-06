import { TCodeConvertor, XmlToReact } from "./utilities";

interface IWidget {
  html: string;
  wordWidget?: boolean;
  convertor?: TCodeConvertor;
}
const Widget = ({ html, wordWidget = false, convertor }: IWidget) => {
  console.log("word", wordWidget);
  const jsx = XmlToReact(html, wordWidget, convertor);
  return <>{jsx}</>;
};

export default Widget;
