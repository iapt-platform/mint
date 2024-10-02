/**
 * 逐词解析意思选择菜单
 * 基本算法：
 * 从redux 获取单词列表。找到与拼写完全相同的单词。按照词典渲染单词意思列表
 * 词典相同语法信息不同的单独一行
 * 在上面的单词数据里面 找到 base 列表，重复上面的步骤
 * 菜单显示结构：
 * 拼写1
 *    词典1  词性  意思1 意思2
 *    词典2  词性  意思1 意思2
 * 拼写2
 *    词典1  词性  意思1 意思2
 *    词典2  词性  意思1 意思2
 *
 */
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Button, Collapse, Tag, Typography } from "antd";

import { IWbw } from "./WbwWord";
import { useAppSelector } from "../../../hooks";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";

const { Panel } = Collapse;
const { Text } = Typography;

interface IMeaning {
  text: string;
  count: number;
}
interface ICase {
  name: string;
  local: string;
  meaning: IMeaning[];
}
interface IDict {
  id: string;
  name?: string;
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
const WbwMeaningSelectWidget = ({ data, onSelect }: IWidget) => {
  const intl = useIntl();
  const inlineDict = useAppSelector(_inlineDict);
  const [parent, setParent] = useState<IParent[]>();

  useEffect(() => {
    //判断单词列表里面是否有这个词
    if (typeof data.real === "undefined" || data.real.value === null) {
      return;
    }
    if (inlineDict.wordIndex.includes(data.real.value)) {
      let baseRemind: string[] = [];
      let baseDone: string[] = [];
      baseRemind.push(data.real.value);
      let mParent: IParent[] = [];
      while (baseRemind.length > 0) {
        const word1 = baseRemind.pop();
        if (typeof word1 === "undefined") {
          break;
        }
        baseDone.push(word1);
        const result1 = inlineDict.wordList.filter(
          (word) => word.word === word1
        );
        mParent.push({ word: word1, dict: [] });
        const indexParent = mParent.findIndex((item) => item.word === word1);
        result1.forEach((value, index, array) => {
          if (
            value.parent &&
            value.parent !== "" &&
            !baseRemind.includes(value.parent) &&
            !baseDone.includes(value.parent)
          ) {
            baseRemind.push(value.parent);
          }
          let indexDict = mParent[indexParent].dict.findIndex(
            (item) => item.id === value.dict_id
          );
          if (indexDict === -1) {
            //没找到，添加一个dict
            mParent[indexParent].dict.push({
              id: value.dict_id,
              name: value.shortname,
              case: [],
            });
            indexDict = mParent[indexParent].dict.findIndex(
              (item) => item.id === value.dict_id
            );
          }
          const wordType = value.type
            ? value.type === ""
              ? "null"
              : value.type.replaceAll(".", "")
            : "null";
          let indexCase = mParent[indexParent].dict[indexDict].case.findIndex(
            (item) => item.name === wordType
          );
          if (indexCase === -1) {
            //没找到，新建
            mParent[indexParent].dict[indexDict].case.push({
              name: wordType,
              local: intl.formatMessage({
                id: `dict.fields.type.${wordType}.short.label`,
                defaultMessage: wordType,
              }),
              meaning: [],
            });
            indexCase = mParent[indexParent].dict[indexDict].case.findIndex(
              (item) => item.name === wordType
            );
          }
          if (value.mean && value.mean.trim() !== "") {
            for (const valueMeaning of value.mean.trim().split("$")) {
              if (valueMeaning.trim() !== "") {
                const mValue = valueMeaning.trim();
                let indexMeaning = mParent[indexParent].dict[indexDict].case[
                  indexCase
                ].meaning.findIndex(
                  (itemMeaning) => itemMeaning.text === mValue
                );

                let indexM: number;
                const currMeanings =
                  mParent[indexParent].dict[indexDict].case[indexCase].meaning;
                for (indexM = 0; indexM < currMeanings.length; indexM++) {
                  if (currMeanings[indexM].text === mValue) {
                    break;
                  }
                }

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
      }

      setParent(mParent);
    }
  }, [data.real?.value, inlineDict]);

  return (
    <div>
      <Collapse defaultActiveKey={["0"]}>
        {parent?.map((item, id) => {
          return (
            <Panel header={item.word} style={{ padding: 0 }} key={id}>
              {item.dict.map((itemDict, idDict) => {
                return (
                  <div key={idDict} style={{ display: "flex" }}>
                    <Text strong style={{ whiteSpace: "nowrap" }}>
                      {itemDict.name}
                    </Text>
                    <div>
                      {itemDict.case.map((itemCase, idCase) => {
                        return (
                          <div key={idCase}>
                            <Tag>{itemCase.local}</Tag>
                            <span>
                              {itemCase.meaning.map(
                                (itemMeaning, idMeaning) => {
                                  return (
                                    <Button
                                      type="text"
                                      size="middle"
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
                                      {itemMeaning.text}
                                    </Button>
                                  );
                                }
                              )}
                            </span>
                          </div>
                        );
                      })}
                    </div>
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

export default WbwMeaningSelectWidget;
