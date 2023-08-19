import { lookup } from "../../reducers/command";
import store from "../../store";
import "./style.css";

interface IWidgetWdCtl {
  text?: string;
}
export const WdCtl = ({ text }: IWidgetWdCtl) => {
  return (
    <>
      {text !== "ti" ? " " : undefined}
      <span
        className="pcd_word"
        onClick={() => {
          //发送点词查询消息
          store.dispatch(lookup(text));
        }}
      >
        {text}
      </span>
    </>
  );
};

interface IWidgetTerm {
  props: string;
}
const WdWidget = ({ props }: IWidgetTerm) => {
  const prop = JSON.parse(atob(props)) as IWidgetWdCtl;
  return <WdCtl {...prop} />;
};

export default WdWidget;
