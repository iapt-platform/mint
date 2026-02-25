import { memo, useMemo } from "react";
import WbwSentCtl, { type IWbwSentCtl } from "../wbw/WbwSentCtl";

interface IWidgetWbwSent {
  props: string;
}

const WbwSentWidget = memo(({ props }: IWidgetWbwSent) => {
  const prop = useMemo(() => JSON.parse(atob(props)) as IWbwSentCtl, [props]);
  return <WbwSentCtl {...prop} />;
});

WbwSentWidget.displayName = "WbwSentWidget";

export default WbwSentWidget;
