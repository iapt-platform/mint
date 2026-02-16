import { useEffect, useState } from "react";

import { useAppSelector } from "../../hooks";
import { message } from "../../reducers/command";

import TermEdit, { ITerm } from "../term/TermEdit";

const TermShellWidget = () => {
  const [termProps, setTermProps] = useState<ITerm>();
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
      <TermEdit {...termProps} />
    </div>
  );
};

export default TermShellWidget;
