import { useState, useEffect } from "react";

import { useAppSelector } from "../../hooks";
import { lookup, lookupWord, myDictIsDirty } from "../../reducers/command";
import store from "../../store";
import Dictionary from "./Dictionary";
import { notification } from "antd";

export interface IWidgetDict {
  word?: string;
}
const DictComponentWidget = ({ word }: IWidgetDict) => {
  const [wordSearch, setWordSearch] = useState(word);
  //接收查字典消息
  const searchWord = useAppSelector(lookupWord);
  const myDictDirty = useAppSelector(myDictIsDirty);

  useEffect(() => {
    console.log("get command", searchWord);

    if (typeof searchWord === "string" && searchWord !== wordSearch) {
      if (myDictDirty) {
        notification.warning({
          message: "用户词典有未保存内容，请保存后再查词",
        });
        return;
      }
      setWordSearch(searchWord);
    }
  }, [searchWord, myDictDirty, wordSearch]);

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
