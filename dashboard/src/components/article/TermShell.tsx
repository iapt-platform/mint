import { useEffect, useState } from "react";

import { useAppSelector } from "../../hooks";
import { message } from "../../reducers/command";

import TermCreate, { IWidgetDictCreate } from "../term/TermCreate";

const Widget = () => {
  const [termProps, setTermProps] = useState<IWidgetDictCreate>();
  //接收术语消息
  const commandMsg = useAppSelector(message);
  useEffect(() => {
    console.log("get command", commandMsg);
    if (commandMsg?.type === "term") {
      setTermProps(commandMsg.prop);
    }
  }, [commandMsg]);
  return (
    <div>
      <TermCreate type="inline" {...termProps} />
    </div>
  );
};

export default Widget;
