import { useState, useEffect } from "react";
import WbwCase from "./WbwCase";
import { bookMarkColor } from "./WbwDetailBookMark";
import WbwFactorMeaning from "./WbwFactorMeaning";
import WbwFactors from "./WbwFactors";
import WbwMeaning from "./WbwMeaning";
import WbwPali from "./WbwPali";
import WbwWord from "./WbwWord";
import "./wbw.css";

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
interface IWidget {
  data: IWbw;
  display?: "block" | "inline";
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
  useEffect(() => {
    setWordData(data);
  }, [data]);
  const styleWbw: React.CSSProperties = {
    display: display === "block" ? "block" : "flex",
  };
  const color = wordData.bookMarkColor
    ? bookMarkColor[wordData.bookMarkColor.value]
    : "unset";
  return (
    <div className={`wbw_word ${display}`} style={styleWbw}>
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
        {fields?.meaning ? (
          <WbwMeaning
            data={wordData}
            onChange={(e: string) => {
              console.log("meaning change", e);
              const newData: IWbw = JSON.parse(JSON.stringify(wordData));
              newData.meaning = { value: [e], status: 5 };
              setWordData(newData);
            }}
          />
        ) : undefined}
        {fields?.factors ? <WbwFactors data={wordData} /> : undefined}
        {fields?.factorMeaning ? (
          <WbwFactorMeaning data={wordData} />
        ) : undefined}
        {fields?.case ? (
          <WbwCase
            data={wordData}
            onSplit={(e: boolean) => {
              console.log("onSplit", wordData.factors?.value);
              if (typeof onSplit !== "undefined") {
                onSplit(e);
              }
            }}
          />
        ) : undefined}
      </div>
    </div>
  );
};

export default Widget;
