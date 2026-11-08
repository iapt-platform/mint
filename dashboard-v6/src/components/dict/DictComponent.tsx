import { useEffect, useState } from "react";

import { useAppSelector } from "../../hooks";
import { lookup, lookupWord, myDictIsDirty } from "../../reducers/command";
import store from "../../store";

import { notification } from "antd";
import Dictionary from "./Dictionary";

export interface IWidgetDict {
  word?: string;
  /**
   * 是否跟随全局查词（redux `lookup`），默认 true。
   * 右边栏开了多个字典 tab 时只有当前 tab 跟随，其余保持各自的词。
   */
  follow?: boolean;
  /** 首次挂载时不显示当前全局查词，从空白开始（新开的 tab 用） */
  blank?: boolean;
  /** 显示的词变化时回调（含首次挂载），给外层做 tab 标题用 */
  onWordChange?: (word?: string) => void;
}

const DictComponentWidget = ({
  word,
  follow = true,
  blank = false,
  onWordChange,
}: IWidgetDict) => {
  const search = useAppSelector(lookupWord);
  const myDictDirty = useAppSelector(myDictIsDirty);

  useEffect(() => {
    // 多个实例时只让跟随的那个提示，避免重复弹出
    if (myDictDirty && follow) {
      notification.warning({
        message: "用户词典有未保存内容，请保存后再查词",
      });
    }
  }, [myDictDirty, follow]);

  const live = typeof search === "string" && !myDictDirty ? search : word;

  // 渲染期派生：全局查词变化时，只有跟随的实例更新自己显示的词
  const [prevLive, setPrevLive] = useState(live);
  const [shown, setShown] = useState(blank ? undefined : live);
  if (live !== prevLive) {
    setPrevLive(live);
    if (follow) setShown(live);
  }

  useEffect(() => {
    onWordChange?.(shown);
  }, [shown, onWordChange]);

  return (
    <Dictionary
      word={shown}
      compact={true}
      onSearch={(value) => {
        console.debug("onSearch", value);
        store.dispatch(lookup(value));
      }}
    />
  );
};

export default DictComponentWidget;
