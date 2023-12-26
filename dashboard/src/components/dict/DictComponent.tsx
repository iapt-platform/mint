import { useState, useEffect } from "react";

import { useAppSelector } from "../../hooks";
import { lookup, lookupWord } from "../../reducers/command";
import store from "../../store";
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
    if (typeof searchWord === "string" && searchWord !== wordSearch) {
      setWordSearch(searchWord);
    }
  }, [searchWord]);

  return (
    <Dictionary
      word={wordSearch}
      compact={true}
      onSearch={(value: string) => {
        console.debug("onSearch", value);
        store.dispatch(lookup(value));
      }}
    />
  );
};

export default DictComponentWidget;
