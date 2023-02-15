import { useState, useEffect } from "react";

import { useAppSelector } from "../../hooks";
import { message } from "../../reducers/command";
import Dictionary from "./Dictionary";

export interface IWidgetDict {
  word?: string;
}
const Widget = ({ word }: IWidgetDict) => {
  const [wordSearch, setWordSearch] = useState(word);
  //接收查字典消息
  const commandMsg = useAppSelector(message);
  useEffect(() => {
    console.log("get command", commandMsg);
    if (commandMsg?.type === "dict") {
      setWordSearch(commandMsg.prop?.word);
    }
  }, [commandMsg]);

  return <Dictionary word={wordSearch} compact={true} />;
};

export default Widget;
