import { IWidgetTermCtl, TermCtl } from "./Term";
import GrammarLookup from "../dict/GrammarLookup";

interface IGrammarTermLookupCtl {
  word?: string;
  term?: IWidgetTermCtl;
}
const GrammarTermLookupCtl = ({ word, term }: IGrammarTermLookupCtl) => {
  return (
    <GrammarLookup word={word}>
      <TermCtl {...term} compact={true} />
    </GrammarLookup>
  );
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IGrammarTermLookupCtl;
  console.debug("QuoteLink", prop);
  return (
    <>
      <GrammarTermLookupCtl {...prop} />
    </>
  );
};

export default Widget;
