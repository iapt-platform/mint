import Mermaid from "../general/Mermaid";

interface IWidgetMermaidCtl {
  text?: string;
}
const MermaidCtl = ({ text }: IWidgetMermaidCtl) => {
  return <Mermaid text={text} />;
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetMermaidCtl;
  return (
    <>
      <MermaidCtl {...prop} />
    </>
  );
};

export default Widget;
