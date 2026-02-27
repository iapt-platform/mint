import { useState, useEffect, useRef } from "react";

import { useAppSelector } from "../../hooks";
import { add, updateIndex, wordIndex } from "../../reducers/inline-dict";
import { get } from "../../request";
import store from "../../store";

import type { IApiResponseDictList } from "../../api/Dict";
import WbwCase from "./WbwCase";

import WbwFactorMeaning from "./WbwFactorMeaning";
import WbwFactors from "./WbwFactors";
import WbwMeaning from "./WbwMeaning";
import WbwPali from "./WbwPali";
import "./wbw.css";
import WbwPara from "./WbwPara";
import WbwPage from "./WbwPage";
import WbwRelationAdd from "./WbwRelationAdd";
import WbwReal from "./WbwReal";
import WbwDetailFm from "./WbwDetailFm";
import type { IStudio } from "../../api/Auth";
import type { ArticleMode } from "../../api/Article";
import {
  WbwStatus,
  type IWbw,
  type IWbwFields,
  type TWbwDisplayMode,
} from "../../types/wbw";
import { bookMarkColor } from "./utils";

interface IWidget {
  data: IWbw;
  answer?: IWbw;
  channelId: string;
  display?: TWbwDisplayMode;
  fields?: IWbwFields;
  mode?: ArticleMode;
  wordDark?: boolean;
  studio?: IStudio;
  readonly?: boolean;
  onChange?: (e: IWbw, isPublish?: boolean, isPublic?: boolean) => void;
  onSplit?: (e: boolean) => void;
}
const WbwWordWidget = ({
  data,
  answer,
  channelId,
  display,
  mode = "edit",
  fields = {
    real: false,
    meaning: true,
    factors: true,
    factorMeaning: true,
    factorMeaning2: false,
    case: true,
  },
  wordDark = false,
  readonly = false,
  studio,
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
    get<IApiResponseDictList>(`/v2/wbwlookup?word=${searchWord.join()}`).then(
      (json) => {
        console.log("lookup ok", json.data.count);
        console.log("time", json.data.time);
        //存储到redux
        store.dispatch(add(json.data.rows));
        store.dispatch(updateIndex(searchWord));
      }
    );

    console.log("lookup", searchWord);
  };

  if (wordData.type?.value === ".ctl.") {
    if (wordData.word.value?.includes("para")) {
      return <WbwPara />;
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
            wordData.real.value &&
            wordData.real.value.length > 0
          ) {
            //开始计时，计时结束查字典
            let words: string[] = [wordData.real.value];
            if (
              wordData.parent &&
              wordData.parent?.value !== "" &&
              wordData.parent?.value !== null
            ) {
              words.push(wordData.parent.value);
            }
            if (
              wordData.factors &&
              wordData.factors?.value !== "" &&
              wordData.factors?.value !== null
            ) {
              words = [
                ...words,
                ...wordData.factors.value.replaceAll("-", "+").split("+"),
              ];
            }
            intervalRef.current = window.setInterval(lookup, 300, words);
          }
        }}
        onMouseLeave={() => {
          stopLookup();
          setShowRelationTool(false);
        }}
      >
        {showRelationTool && data.real.value ? (
          <WbwRelationAdd data={data} />
        ) : undefined}
        <WbwPali
          key="pali"
          data={wordData}
          channelId={channelId}
          mode={mode}
          display={display}
          studio={studio}
          readonly={readonly}
          onSave={(e: IWbw, isPublish: boolean, isPublic: boolean) => {
            const newData: IWbw = JSON.parse(JSON.stringify(e));
            setWordData(newData);
            if (typeof onChange !== "undefined") {
              onChange(e, isPublish, isPublic);
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
            <WbwReal key="real" data={wordData} display={display} />
          ) : undefined}
          {fieldDisplay?.meaning ? (
            <WbwMeaning
              key="meaning"
              mode={mode}
              data={wordData}
              answer={answer}
              display={display}
              onChange={(value?: string | null) => {
                if (value) {
                  const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                  newData.meaning = { value: value, status: WbwStatus.manual };
                  setWordData(newData);
                  if (typeof onChange !== "undefined") {
                    onChange(newData, false, false);
                  }
                }
              }}
            />
          ) : undefined}
          {fieldDisplay?.factors ? (
            <WbwFactors
              key="factors"
              data={wordData}
              answer={answer}
              display={display}
              onChange={(e: string) => {
                console.log("factor change", e);
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.factors = { value: e, status: 5 };
                setNewFactors(e);
                setWordData(newData);
                if (typeof onChange !== "undefined") {
                  onChange(newData, false, false);
                }
              }}
            />
          ) : undefined}
          {fieldDisplay?.factorMeaning ? (
            <WbwFactorMeaning
              key="fm"
              data={wordData}
              answer={answer}
              display={display}
              factors={newFactors}
              onChange={(e: string) => {
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.factorMeaning = { value: e, status: 5 };
                setWordData(newData);
                if (typeof onChange !== "undefined") {
                  onChange(newData, false, false);
                }
              }}
            />
          ) : undefined}
          {fieldDisplay?.factorMeaning2 ? (
            <WbwDetailFm
              factors={wordData.factors?.value?.split("+")}
              value={wordData.factorMeaning?.value?.split("+")}
              onChange={(value: string[]) => {
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.factorMeaning = {
                  value: value.join("+"),
                  status: WbwStatus.manual,
                };
                setWordData(newData);
                if (typeof onChange !== "undefined") {
                  onChange(newData);
                }
              }}
              onJoin={(value: string) => {
                const newData: IWbw = JSON.parse(JSON.stringify(wordData));
                newData.meaning = { value: value, status: WbwStatus.manual };
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
              answer={answer}
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
