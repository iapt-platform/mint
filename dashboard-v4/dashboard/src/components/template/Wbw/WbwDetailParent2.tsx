import { AutoComplete, Form, Input, Select } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { useAppSelector } from "../../../hooks";
import { IApiResponseDictData } from "../../api/Dict";
import { IWbw } from "./WbwWord";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";

interface ValueType {
  key?: string;
  label: React.ReactNode;
  value: string | number;
}

interface IWidget {
  data: IWbw;
  onChange?: Function;
}
const WbwParent2Widget = ({ data, onChange }: IWidget) => {
  const intl = useIntl();
  const [parentOptions, setParentOptions] = useState<ValueType[]>([]);
  const inlineDict = useAppSelector(_inlineDict);

  const getParentInDict = (
    wordIn: string,
    wordIndex: string[],
    wordList: IApiResponseDictData[]
  ): string[] => {
    if (wordIndex.includes(wordIn)) {
      const result = wordList.filter((word) => word.word === wordIn);
      //查重
      //TODO 加入信心指数并排序
      let myMap = new Map<string, number>();
      let parent: string[] = [];
      for (const iterator of result) {
        if (iterator.parent) {
          myMap.set(iterator.parent, 1);
        }
      }
      myMap.forEach((value, key, map) => {
        parent.push(key);
      });
      return parent;
    } else {
      return [];
    }
  };

  useEffect(() => {
    if (typeof data.parent?.value !== "string") {
      return;
    }
    const parent = getParentInDict(
      data.parent.value,
      inlineDict.wordIndex,
      inlineDict.wordList
    );
    const parentOptions = parent.map((item) => {
      return {
        label: item,
        value: item,
      };
    });
    setParentOptions(parentOptions);
  }, [inlineDict, data]);

  const grammar = ["prp", "pp", "fpp", "pass", "caus", "vdn"];
  const options = grammar.map((item) => {
    return {
      value: `.${item}.`,
      label: intl.formatMessage({
        id: `dict.fields.type.${item}.label`,
        defaultMessage: item,
      }),
    };
  });
  return (
    <>
      <div style={{ display: "flex" }}>
        <Form.Item
          name="parent2"
          label={intl.formatMessage({ id: "forms.fields.parent2.label" })}
          tooltip={intl.formatMessage({
            id: "forms.fields.parent2.tooltip",
          })}
        >
          <AutoComplete
            options={parentOptions}
            onChange={(value: any) => {
              if (typeof onChange !== "undefined") {
                onChange({ field: "parent2", value: value });
              }
            }}
          >
            <Input allowClear />
          </AutoComplete>
        </Form.Item>
        <Form.Item name="grammar2" noStyle>
          <Select
            style={{ width: 100 }}
            allowClear
            options={options}
            onChange={(value: string) => {
              if (typeof onChange !== "undefined") {
                onChange({ field: "grammar2", value: value });
              }
            }}
          />
        </Form.Item>
      </div>
    </>
  );
};

export default WbwParent2Widget;
