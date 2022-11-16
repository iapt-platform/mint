import { Popover } from "antd";

interface IWidgetWdCtl {
  text?: string;
}
const WdCtl = ({ text }: IWidgetWdCtl) => {
  const noteCard = "note";
  return (
    <Popover content={noteCard} placement="bottom">
      {text}{" "}
    </Popover>
  );
};

interface IWidgetTerm {
  props: string;
}
const Widget = ({ props }: IWidgetTerm) => {
  const prop = JSON.parse(atob(props)) as IWidgetWdCtl;
  return <WdCtl {...prop} />;
};

export default Widget;
