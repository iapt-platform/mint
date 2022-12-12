import { useState, useEffect } from "react";
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
interface WbwElement {
  value: string;
  status: WbwStatus;
}
interface WbwElement2 {
  value: string[];
  status: WbwStatus;
}
interface WbwElement3 {
  value: number;
  status: WbwStatus;
}
export interface IWbw {
  word: WbwElement;
  real?: WbwElement;
  meaning?: WbwElement2;
  type?: WbwElement;
  grammar?: WbwElement;
  style?: WbwElement;
  case?: WbwElement2;
  parent?: WbwElement;
  factors?: WbwElement;
  factorMeaning?: WbwElement;
  relation?: WbwElement;
  note?: WbwElement;
  bookMarkColor?: WbwElement3;
  bookMarkText?: WbwElement;
  locked?: boolean;
  confidence: number;
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
  useEffect(() => {
    setWordData(data);
    setFieldDisplay(fields);
    console.log("display change", fields);
  }, [data, fields]);

  const color = wordData.bookMarkColor
    ? bookMarkColor[wordData.bookMarkColor.value]
    : "unset";
  const wbwCtl = wordData.type?.value === ".ctl." ? "wbw_ctl" : "";
  const wbwAnchor = wordData.grammar?.value === ".a." ? "wbw_anchor" : "";

  const styleWbw: React.CSSProperties = {
    display: display === "block" ? "block" : "flex",
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
      >
        <WbwPali
          data={wordData}
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
                setWordData(newData);
              }}
            />
          ) : undefined}
          {fieldDisplay?.factorMeaning ? (
            <WbwFactorMeaning
              data={wordData}
              display={display}
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
                newData.case = { value: e.split("+"), status: 5 };
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
