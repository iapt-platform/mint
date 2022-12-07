import { useState } from "react";
import WbwWord, { IWbw, IWbwFields } from "./Wbw/WbwWord";

interface IWidget {
  data: IWbw[];
  display?: "block" | "inline";
  fields?: IWbwFields;
}
const Widget = ({ data, display, fields }: IWidget) => {
  const [wordData, setWordData] = useState(data);

  return (
    <div style={{ display: "flex", flexWrap: "wrap" }}>
      {wordData.map((item, id) => {
        return (
          <WbwWord
            data={item}
            key={id}
            display={display}
            fields={fields}
            onChange={(e: IWbw) => {
              console.log("word changed", e);
              console.log("word id", id);
              //TODO update
            }}
            onSplit={() => {
              console.log("word id", id, wordData[id].factors?.value);
              const newData: IWbw[] = JSON.parse(JSON.stringify(wordData));

              const children: IWbw[] | undefined = wordData[id].factors?.value
                .split("+")
                .map((item, id) => {
                  return {
                    word: { value: item, status: 5 },
                    real: { value: item, status: 5 },
                    confidence: 1,
                  };
                });
              if (typeof children !== "undefined") {
                console.log("children", children);
                newData.splice(id + 1, 0, ...children);
                console.log("new-data", newData);
                setWordData(newData);
              }
            }}
          />
        );
      })}
    </div>
  );
};

export default Widget;
