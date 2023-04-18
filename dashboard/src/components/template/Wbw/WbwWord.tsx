import { useState, useEffect, useRef } from "react";
import type { UploadFile } from "antd/es/upload/interface";

import { useAppSelector } from "../../../hooks";
import { add, wordIndex } from "../../../reducers/inline-dict";
import { get } from "../../../request";
import store from "../../../store";

import { IApiResponseDictList } from "../../api/Dict";
import WbwCase from "./WbwCase";
import { bookMarkColor } from "./WbwDetailBookMark";
import WbwFactorMeaning from "./WbwFactorMeaning";
import WbwFactors from "./WbwFactors";
import WbwMeaning from "./WbwMeaning";
import WbwPali from "./WbwPali";
import "./wbw.css";
import WbwPara from "./WbwPara";
import WbwPage from "./WbwPage";

export type TFieldName =
  | "word"
  | "real"
  | "meaning"
  | "type"
  | "grammar"
  | "case"
  | "parent"
  | "factors"
  | "factorMeaning"
  | "relation"
  | "note"
  | "bookMarkColor"
  | "bookMarkText"
  | "locked"
  | "confidence";

export interface IWbwField {
  field: TFieldName;
  value: string;
}

enum WbwStatus {
  initiate = 0,
  auto = 3,
  manual = 5,
}
interface WbwElement<R> {
  value: R;
  status: WbwStatus;
}

export interface IWbw {
  uid?: string;
  word: WbwElement<string>;
  real?: WbwElement<string>;
  meaning?: WbwElement<string[]>;
  type?: WbwElement<string>;
  grammar?: WbwElement<string>;
  style?: WbwElement<string>;
  case?: WbwElement<string[]>;
  parent?: WbwElement<string>;
  factors?: WbwElement<string>;
  factorMeaning?: WbwElement<string>;
  relation?: WbwElement<string>;
  note?: WbwElement<string>;
  bookMarkColor?: WbwElement<number>;
  bookMarkText?: WbwElement<string>;
  locked?: boolean;
  confidence: number;
  attachments?: UploadFile[];
  hasComment?: boolean;
}
export interface IWbwFields {
  meaning?: boolean;
  factors?: boolean;
  factorMeaning?: boolean;
  case?: boolean;
}
export type TWbwDisplayMode = "block" | "inline";
interface IWidget {
  data: IWbw;
  display?: TWbwDisplayMode;
  fields?: IWbwFields;
  onChange?: Function;
  onSplit?: Function;
}
const Widget = ({
  data,
  display = "block",
  fields = { meaning: true, factors: true, factorMeaning: true, case: true },
  onChange,
  onSplit,
}: IWidget) => {
  const [wordData, setWordData] = useState(data);
  const [fieldDisplay, setFieldDisplay] = useState(fields);
  const [newFactors, setNewFactors] = useState<string>();
  const intervalRef = useRef<number | null>(null); //防抖计时器句柄
  const inlineWordIndex = useAppSelector(wordIndex);

  useEffect(() => {
    setWordData(data);
    setFieldDisplay(fields);
  }, [data, fields]);

  const color = wordData.bookMarkColor
    ? bookMarkColor[wordData.bookMarkColor.value]
    : "unset";
  const wbwCtl = wordData.type?.value === ".ctl." ? "wbw_ctl" : "";
  const wbwAnchor = wordData.grammar?.value === ".a." ? "wbw_anchor" : "";

  const styleWbw: React.CSSProperties = {
    display: display === "block" ? "block" : "flex",
  };

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
  const lookup = (word: string) => {
    stopLookup();
    //查询这个词在内存字典里是否有
    if (inlineWordIndex.includes(word)) {
      //已经有了，退出
      return;
    }
    get<IApiResponseDictList>(`/v2/wbwlookup?word=${word}`).then((json) => {
      console.log("lookup ok", json.data.count);
      //存储到redux
      store.dispatch(add(json.data.rows));
    });

    console.log("lookup", word);
  };

  if (wordData.type?.value === ".ctl.") {
    if (wordData.word.value.includes("para")) {
      return <WbwPara data={wordData} />;
    } else {
      return <WbwPage data={wordData} />;
    }
  } else {
    return (
      <div
        className={`wbw_word ${display} ${wbwCtl} ${wbwAnchor} `}
        style={styleWbw}
        onMouseEnter={() => {
          if (intervalRef.current === null) {
            //开始计时，计时结束查字典
            intervalRef.current = window.setInterval(
              lookup,
              200,
              wordData.word.value
            );
          }
        }}
        onMouseLeave={() => {
          stopLookup();
        }}
      >
        <WbwPali
          data={wordData}
          display={display}
          onSave={(e: IWbw) => {
            console.log("save", e);
            const newData: IWbw = JSON.parse(JSON.stringify(e));
            setWordData(newData);
            if (typeof onChange !== "undefined") {
              onChange(e);
            }
          }}
        />
        <div
          className="wbw_body"
          style={{
            background: `linear-gradient(90deg, rgba(255, 255, 255, 0), ${color})`,
          }}
        >
          {fieldDisplay?.meaning ? (
            <WbwMeaning
              data={wordData}
              display={display}
              onChange={(e: string) => {
                console.log("meaning change", e);
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.meaning = { value: [e], status: 5 };
                setWordData(newData);
                if (typeof onChange !== "undefined") {
                  onChange(newData);
                }
              }}
            />
          ) : undefined}
          {fieldDisplay?.factors ? (
            <WbwFactors
              data={wordData}
              display={display}
              onChange={(e: string) => {
                console.log("factor change", e);
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.factors = { value: e, status: 5 };
                setNewFactors(e);
                setWordData(newData);
              }}
            />
          ) : undefined}
          {fieldDisplay?.factorMeaning ? (
            <WbwFactorMeaning
              data={wordData}
              display={display}
              factors={newFactors}
              onChange={(e: string) => {
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.factorMeaning = { value: e, status: 5 };
                setWordData(newData);
              }}
            />
          ) : undefined}
          {fieldDisplay?.case ? (
            <WbwCase
              data={wordData}
              display={display}
              onSplit={(e: boolean) => {
                console.log("onSplit", wordData.factors?.value);
                if (typeof onSplit !== "undefined") {
                  onSplit(e);
                }
              }}
              onChange={(e: string) => {
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.case = { value: e.split("$"), status: 5 };
                setWordData(newData);
              }}
            />
          ) : undefined}
        </div>
      </div>
    );
  }
};

export default Widget;
