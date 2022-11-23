import Note from "./Note";
import Quote from "./Quote";
import SentEdit from "./SentEdit";
import SentRead from "./SentRead";
import Term from "./Term";
import Wd from "./Wd";

interface IWidgetMdTpl {
  tpl?: string;
  props?: string;
}
const Widget = ({ tpl, props }: IWidgetMdTpl) => {
  switch (tpl) {
    case "term":
      return <Term props={props ? props : ""} />;
    case "note":
      return <Note props={props ? props : ""} />;
    case "sentread":
      return <SentRead props={props ? props : ""} />;
    case "sentedit":
      return <SentEdit props={props ? props : ""} />;
    case "wd":
      return <Wd props={props ? props : ""} />;
    case "quote":
      return <Quote props={props ? props : ""} />;
    default:
      return <>未定义模版({tpl})</>;
  }
};

export default Widget;
