import { useEffect } from "react";

import { useAppSelector } from "../../hooks";
import { lookup, lookupWord, myDictIsDirty } from "../../reducers/command";
import store from "../../store";

import { notification } from "antd";
import Dictionary from "./Dictionary";

export interface IWidgetDict {
  word?: string;
}

const DictComponentWidget = ({ word }: IWidgetDict) => {
  const search = useAppSelector(lookupWord);
  const myDictDirty = useAppSelector(myDictIsDirty);

  useEffect(() => {
    if (myDictDirty) {
      notification.warning({
        message: "用户词典有未保存内容，请保存后再查词",
      });
    }
  }, [myDictDirty]);

  // 直接从 redux state 派生展示的词，无需本地 state
  const wordSearch = typeof search === "string" && !myDictDirty ? search : word;

  return (
    <Dictionary
      word={wordSearch}
      compact={true}
      onSearch={(value) => {
        console.debug("onSearch", value);
        store.dispatch(lookup(value));
      }}
    />
  );
};

export default DictComponentWidget;
