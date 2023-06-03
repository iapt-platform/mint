import { useState, useEffect } from "react";

import { useAppSelector } from "../../hooks";
import { lookupWord } from "../../reducers/command";
import Dictionary from "./Dictionary";

export interface IWidgetDict {
  word?: string;
}
const DictComponentWidget = ({ word }: IWidgetDict) => {
  const [wordSearch, setWordSearch] = useState(word);
  //接收查字典消息
  const searchWord = useAppSelector(lookupWord);
  useEffect(() => {
    console.log("get command", searchWord);
    if (typeof searchWord === "string") {
      setWordSearch(searchWord);
    }
  }, [searchWord]);

  return <Dictionary word={wordSearch} compact={true} />;
};

export default DictComponentWidget;
