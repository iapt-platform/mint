import type { IWidgetSentReadFrame } from "../sentence-editor/SentRead";
import SentReadFrame from "../sentence-editor/SentRead";

interface IWidget {
  props: string;
}

const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetSentReadFrame;
  return <SentReadFrame {...prop} />;
};

export default Widget;
