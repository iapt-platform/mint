import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Collapse, Tag } from "antd";

import { IWbw } from "./WbwWord";
import { useAppSelector } from "../../../hooks";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";

const { Panel } = Collapse;

interface IMeaning {
  text: string;
  count: number;
}
interface ICase {
  name: string;
  meaning: IMeaning[];
}
interface IDict {
  name: string;
  case: ICase[];
}
interface IParent {
  word: string;
  dict: IDict[];
}

interface IWidget {
  data: IWbw;
  onSelect?: Function;
}
const Widget = ({ data, onSelect }: IWidget) => {
  const intl = useIntl();
  const inlineDict = useAppSelector(_inlineDict);
  const [parent, setParent] = useState<IParent[]>();

  useEffect(() => {
    if (inlineDict.wordIndex.includes(data.word.value)) {
      let mParent: IParent[] = [];
      const word1 = data.word.value;
      const result1 = inlineDict.wordList.filter((word) => word.word === word1);
      mParent.push({ word: word1, dict: [] });
      const indexParent = mParent.findIndex((item) => item.word === word1);
      result1.forEach((value, index, array) => {
        let indexDict = mParent[indexParent].dict.findIndex(
          (item) => item.name === value.dict_id
        );
        if (indexDict === -1) {
          //没找到，添加一个dict
          mParent[indexParent].dict.push({ name: value.dict_id, case: [] });
          indexDict = mParent[indexParent].dict.findIndex(
            (item) => item.name === value.dict_id
          );
        }
        const wordType = value.type === "" ? "null" : value.type;
        let indexCase = mParent[indexParent].dict[indexDict].case.findIndex(
          (item) => item.name === wordType
        );
        if (indexCase === -1) {
          mParent[indexParent].dict[indexDict].case.push({
            name: wordType,
            meaning: [],
          });
          indexCase = mParent[indexParent].dict[indexDict].case.findIndex(
            (item) => item.name === wordType
          );
        }
        console.log("indexCase", indexCase, value.mean);
        if (value.mean && value.mean.trim() !== "") {
          for (const valueMeaning of value.mean.trim().split("$")) {
            if (valueMeaning.trim() !== "") {
              const mValue = valueMeaning.trim();
              console.log("meaning", mValue);
              console.log(
                "in",
                mParent[indexParent].dict[indexDict].case[indexCase].meaning
              );
              let indexMeaning = mParent[indexParent].dict[indexDict].case[
                indexCase
              ].meaning.findIndex((itemMeaning) => itemMeaning.text === mValue);

              console.log("indexMeaning", indexMeaning);
              let indexM: number;
              const currMeanings =
                mParent[indexParent].dict[indexDict].case[indexCase].meaning;
              for (indexM = 0; indexM < currMeanings.length; indexM++) {
                console.log("index", indexM);
                console.log("array", currMeanings);
                console.log("word1", currMeanings[indexM].text);
                console.log("word2", mValue);
                if (currMeanings[indexM].text === mValue) {
                  break;
                }
              }
              console.log("new index", indexM);
              if (indexMeaning === -1) {
                mParent[indexParent].dict[indexDict].case[
                  indexCase
                ].meaning.push({
                  text: mValue,
                  count: 1,
                });
              } else {
                mParent[indexParent].dict[indexDict].case[indexCase].meaning[
                  indexMeaning
                ].count++;
              }
            }
          }
        }
      });

      setParent(mParent);
    }
  }, [inlineDict]);
  /*
  const meaning: IMeaning[] = Array.from(Array(10).keys()).map((item) => {
    return { text: "意思" + item, count: item };
  });
  const dict: IDict[] = Array.from(Array(3).keys()).map((item) => {
    return { name: "字典" + item, meaning: meaning };
  });
  const parent: IParent[] = Array.from(Array(3).keys()).map((item) => {
    return { word: data.word.value + item, dict: dict };
  });
  */
  return (
    <div>
      <Collapse defaultActiveKey={["0"]}>
        {parent?.map((item, id) => {
          return (
            <Panel header={item.word} style={{ padding: 0 }} key={id}>
              {item.dict.map((itemDict, idDict) => {
                return (
                  <div key={idDict}>
                    <div>{itemDict.name}</div>
                    {itemDict.case.map((itemCase, idCase) => {
                      return (
                        <div key={idCase}>
                          <div>{itemCase.name}</div>
                          <div>
                            {itemCase.meaning.map((itemMeaning, idMeaning) => {
                              return (
                                <Tag
                                  key={idMeaning}
                                  onClick={(
                                    e: React.MouseEvent<HTMLAnchorElement>
                                  ) => {
                                    e.preventDefault();
                                    if (typeof onSelect !== "undefined") {
                                      onSelect(itemMeaning.text);
                                    }
                                  }}
                                >
                                  {itemMeaning.text}-{itemMeaning.count}
                                </Tag>
                              );
                            })}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                );
              })}
            </Panel>
          );
        })}
      </Collapse>
    </div>
  );
};

export default Widget;
