import { useEffect, useRef } from "react";
import { useAppSelector } from "../../../hooks";
import { add, updateIndex, wordIndex } from "../../../reducers/inline-dict";
import { get } from "../../../request";
import { IApiResponseDictList } from "../../api/Dict";
import store from "../../../store";

interface IWidget {
  run?: boolean;
  words?: string[];
  delay?: number;
}
const WbwLookup = ({ words, run = false, delay = 300 }: IWidget) => {
  const inlineWordIndex = useAppSelector(wordIndex);

  const intervalRef = useRef<number | null>(null); //防抖计时器句柄

  useEffect(() => {
    // 监听store中的words变化
    if (run && words && words.length > 0) {
      // 开始查字典
      intervalRef.current = window.setInterval(lookup, delay, words);
    } else {
      stopLookup();
    }
    return () => {
      // 组件销毁时清除计时器
      clearInterval(intervalRef.current!);
    };
  }, [run, words]);
  /**
   * 停止查字典计时
   * 在两种情况下停止计时
   * 1. 开始查字典
   * 2. 防抖时间内鼠标移出单词区
   */
  const stopLookup = () => {
    if (intervalRef.current) {
      window.clearInterval(intervalRef.current);
      intervalRef.current = null;
    }
  };
  /**
   * 查字典
   * @param word 要查的单词
   */
  const lookup = (words: string[]) => {
    stopLookup();

    //查询这个词在内存字典里是否有
    const searchWord = words.filter((value) => {
      if (inlineWordIndex.includes(value)) {
        //已经有了
        return false;
      } else {
        return true;
      }
    });
    if (searchWord.length === 0) {
      return;
    }
    const url = `/v2/wbwlookup?word=${searchWord.join()}`;
    console.info("api request", url);
    get<IApiResponseDictList>(url).then((json) => {
      console.debug("api response", json);
      //存储到redux
      store.dispatch(add(json.data.rows));
      store.dispatch(updateIndex(searchWord));
    });

    console.log("lookup", searchWord);
  };
  return <></>;
};

export default WbwLookup;
