import { marked } from "marked";

interface IWidget {
  text?: string;
}
const Widget = ({ text }: IWidget) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: marked.parse(text ? text : ""),
      }}
    />
  );
};

export default Widget;
