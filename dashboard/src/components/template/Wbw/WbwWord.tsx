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
import WbwRelationAdd from "./WbwRelationAdd";
import { ArticleMode } from "../../article/Article";
import WbwReal from "./WbwReal";

export type TFieldName =
  | "word"
  | "real"
  | "meaning"
  | "type"
  | "grammar"
  | "grammar2"
  | "case"
  | "parent"
  | "parent2"
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

export enum WbwStatus {
  initiate = 0,
  auto = 3,
  apply = 5,
  manual = 7,
}
export interface WbwElement<R> {
  value: R;
  status: WbwStatus;
}

export interface IWbw {
  uid?: string;
  book: number;
  para: number;
  sn: number[];
  word: WbwElement<string>;
  real: WbwElement<string>;
  meaning?: WbwElement<string | null>;
  type?: WbwElement<string | null>;
  grammar?: WbwElement<string | null>;
  style?: WbwElement<string | null>;
  case?: WbwElement<string | null>;
  parent?: WbwElement<string | null>;
  parent2?: WbwElement<string | null>;
  grammar2?: WbwElement<string | null>;
  factors?: WbwElement<string | null>;
  factorMeaning?: WbwElement<string | null>;
  relation?: WbwElement<string | null>;
  note?: WbwElement<string | null>;
  bookMarkColor?: WbwElement<number | null>;
  bookMarkText?: WbwElement<string | null>;
  locked?: boolean;
  confidence: number;
  attachments?: UploadFile[];
  hasComment?: boolean;
}
export interface IWbwFields {
  real?: boolean;
  meaning?: boolean;
  factors?: boolean;
  factorMeaning?: boolean;
  case?: boolean;
}

export type TWbwDisplayMode = "block" | "inline" | "list";
interface IWidget {
  data: IWbw;
  display?: TWbwDisplayMode;
  fields?: IWbwFields;
  mode?: ArticleMode;
  wordDark?: boolean;
  onChange?: Function;
  onSplit?: Function;
}
const WbwWordWidget = ({
  data,
  display = "block",
  mode = "edit",
  fields = {
    real: false,
    meaning: true,
    factors: true,
    factorMeaning: true,
    case: true,
  },
  wordDark = false,
  onChange,
  onSplit,
}: IWidget) => {
  const [wordData, setWordData] = useState(data);
  const fieldDisplay = fields;
  const [newFactors, setNewFactors] = useState<string>();
  const [showRelationTool, setShowRelationTool] = useState(false);
  const intervalRef = useRef<number | null>(null); //防抖计时器句柄
  const inlineWordIndex = useAppSelector(wordIndex);

  useEffect(() => {
    setWordData(data);
  }, [data]);

  const color = wordData.bookMarkColor?.value
    ? bookMarkColor[wordData.bookMarkColor.value]
    : "unset";
  const wbwCtl = wordData.type?.value === ".ctl." ? "wbw_ctl" : "";
  const wbwAnchor = wordData.grammar?.value === ".a." ? "wbw_anchor" : "";
  const wbwDark = wordDark ? "dark" : "";

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
      console.log("time", json.data.time);
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
        className={`wbw_word ${display}_${mode} display_${display} ${wbwCtl} ${wbwAnchor} ${wbwDark} `}
        style={styleWbw}
        onMouseEnter={() => {
          setShowRelationTool(true);
          if (
            intervalRef.current === null &&
            wordData.real &&
            wordData.real.value?.length > 0
          ) {
            //开始计时，计时结束查字典
            intervalRef.current = window.setInterval(
              lookup,
              300,
              wordData.real.value
            );
          }
        }}
        onMouseLeave={() => {
          stopLookup();
          setShowRelationTool(false);
        }}
      >
        {showRelationTool ? <WbwRelationAdd data={data} /> : undefined}
        <WbwPali
          key="pali"
          data={wordData}
          display={display}
          onSave={(e: IWbw, isPublish: boolean) => {
            const newData: IWbw = JSON.parse(JSON.stringify(e));
            setWordData(newData);
            if (typeof onChange !== "undefined") {
              onChange(e, isPublish);
            }
          }}
        />
        <div
          className="wbw_body"
          style={{
            background: `linear-gradient(90deg, rgba(255, 255, 255, 0), ${color})`,
          }}
        >
          {fieldDisplay?.real ? (
            <WbwReal
              key="real"
              data={wordData}
              display={display}
              onChange={(e: string) => {
                console.log("meaning change", e);
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.meaning = { value: e, status: 5 };
                setWordData(newData);
                if (typeof onChange !== "undefined") {
                  onChange(newData);
                }
              }}
            />
          ) : undefined}
          {fieldDisplay?.meaning ? (
            <WbwMeaning
              key="meaning"
              mode={mode}
              data={wordData}
              display={display}
              onChange={(e: string) => {
                console.log("meaning change", e);
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.meaning = { value: e, status: WbwStatus.manual };
                setWordData(newData);
                if (typeof onChange !== "undefined") {
                  onChange(newData);
                }
              }}
            />
          ) : undefined}
          {fieldDisplay?.factors ? (
            <WbwFactors
              key="factors"
              data={wordData}
              display={display}
              onChange={(e: string) => {
                console.log("factor change", e);
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.factors = { value: e, status: 5 };
                setNewFactors(e);
                setWordData(newData);
                if (typeof onChange !== "undefined") {
                  onChange(newData);
                }
              }}
            />
          ) : undefined}
          {fieldDisplay?.factorMeaning ? (
            <WbwFactorMeaning
              key="fm"
              data={wordData}
              display={display}
              factors={newFactors}
              onChange={(e: string) => {
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.factorMeaning = { value: e, status: 5 };
                setWordData(newData);
                if (typeof onChange !== "undefined") {
                  onChange(newData);
                }
              }}
            />
          ) : undefined}
          {fieldDisplay?.case ? (
            <WbwCase
              key="case"
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
                newData.case = { value: e, status: 7 };
                setWordData(newData);
                if (typeof onChange !== "undefined") {
                  onChange(newData);
                }
              }}
            />
          ) : undefined}
        </div>
      </div>
    );
  }
};

export default WbwWordWidget;
