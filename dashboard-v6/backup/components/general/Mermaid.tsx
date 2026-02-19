import lodash from "lodash";
import mermaid from "mermaid";

interface IWidget {
  text?: string;
}
const MermaidWidget = ({ text }: IWidget) => {
  const id = lodash.times(20, () => lodash.random(35).toString(36)).join("");
  const graph = mermaid.render(`g-${id}`, text ? text : "");

  return (
    <div
      dangerouslySetInnerHTML={{
        __html: graph,
      }}
    />
  );
};

export default MermaidWidget;
