import { grammar } from "../../reducers/command";
import { openPanel } from "../../reducers/right-panel";
import store from "../../store";

interface IWidget {
  word?: string;
  children?: React.ReactNode;
}
const GrammarLookup = ({ word, children }: IWidget) => {
  return (
    <span
      onClick={() => {
        store.dispatch(grammar(word));
        store.dispatch(openPanel("grammar"));
      }}
    >
      {children}
    </span>
  );
};

export default GrammarLookup;
