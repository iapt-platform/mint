import Marked from "../general/Marked";

interface IWidget {
  content: string;
}
const TplCtl = ({ content }: IWidget) => {
  return <Marked text={content} />;
};

interface IWidgetTerm {
  props: string;
}
const Widget = ({ props }: IWidgetTerm) => {
  const prop = JSON.parse(atob(props)) as IWidget;
  return (
    <>
      <TplCtl {...prop} />
    </>
  );
};

export default Widget;
