import GrammarPop, { GrammarPopShell } from "../dict/GrammarPop";
import Article from "./Article";
import Exercise from "./Exercise";
import Mermaid from "./Mermaid";
import Nissaya from "./Nissaya";
import Note from "./Note";
import ParaHandle from "./ParaHandle";
import Quote from "./Quote";
import SentEdit from "./SentEdit";
import SentRead from "./SentRead";
import Term from "./Term";
import Toggle from "./Toggle";
import WbwSent from "./WbwSent";
import Wd from "./Wd";

interface IWidgetMdTpl {
  tpl?: string;
  props?: string;
  children?: React.ReactNode;
}
const Widget = ({ tpl, props, children }: IWidgetMdTpl) => {
  switch (tpl) {
    case "term":
      return <Term props={props ? props : ""} />;
    case "note":
      return <Note props={props ? props : ""}>{children}</Note>;
    case "sentread":
      return <SentRead props={props ? props : ""} />;
    case "sentedit":
      return <SentEdit props={props ? props : ""} />;
    case "wbw_sent":
      return <WbwSent props={props ? props : ""} />;
    case "wd":
      return <Wd props={props ? props : ""} />;
    case "quote":
      return <Quote props={props ? props : ""} />;
    case "exercise":
      return <Exercise props={props ? props : ""}>{children}</Exercise>;
    case "article":
      return <Article props={props ? props : ""} />;
    case "nissaya":
      return <Nissaya props={props ? props : ""} />;
    case "toggle":
      return <Toggle props={props ? props : undefined}>{children}</Toggle>;
    case "para":
      return <ParaHandle props={props ? props : ""} />;
    case "mermaid":
      return <Mermaid props={props ? props : ""} />;
    case "grammar-pop":
      return <GrammarPopShell props={props ? props : ""} />;
    default:
      return <>未定义模版({tpl})</>;
  }
};

export default Widget;
