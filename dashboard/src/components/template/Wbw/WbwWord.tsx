import { useState } from "react";
import WbwCase from "./WbwCase";
import WbwFactorMeaning from "./WbwFactorMeaning";
import WbwFactors from "./WbwFactors";
import WbwMeaning from "./WbwMeaning";
import WbwPali from "./WbwPali";

type FieldName =
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
  | "confidence";

export interface IWbwField {
  field: FieldName;
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
  bookMarkColor?: WbwElement;
  bookMarkText?: WbwElement;
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
}
const Widget = ({
  data,
  display = "block",
  fields = { meaning: true, factors: true, factorMeaning: true, case: true },
  onChange,
}: IWidget) => {
  const [wordData, setWordData] = useState(data);

  const styleWbw: React.CSSProperties = {
    display: display === "block" ? "block" : "flex",
  };
  return (
    <div style={styleWbw}>
      <WbwPali
        data={wordData}
        onChange={(e: IWbw) => {
          //setWordData(e);
          if (typeof onChange !== "undefined") {
            // onChange(e);
          }
        }}
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
        style={{
          background: `linear-gradient(90deg, rgba(255, 255, 255, 0), ${wordData.bookMarkColor?.value})`,
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
        {fields?.case ? <WbwCase data={wordData} /> : undefined}
      </div>
    </div>
  );
};

export default Widget;
