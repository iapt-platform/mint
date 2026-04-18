import type { IWidgetSentReadFrame } from "../sentence/SentRead";
import SentReadFrame from "../sentence/SentRead";

interface IWidget {
  props: string;
}

const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetSentReadFrame;
  return <SentReadFrame {...prop} />;
};

export default Widget;
