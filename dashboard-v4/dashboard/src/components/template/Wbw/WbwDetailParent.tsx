import { AutoComplete, Input } from "antd";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../../hooks";

import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";

import { IWbw } from "./WbwWord";
import { IApiResponseDictData } from "../../api/Dict";

export const getParentInDict = (
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

interface ValueType {
  key?: string;
  label: React.ReactNode;
  value: string | number;
}
interface IWidget {
  data: IWbw;
  readonly?: boolean;
  onChange?: Function;
}
const WbwDetailParentWidget = ({
  data,
  readonly = false,
  onChange,
}: IWidget) => {
  const [parentOptions, setParentOptions] = useState<ValueType[]>([]);
  const inlineDict = useAppSelector(_inlineDict);

  useEffect(() => {
    if (!data.real.value) {
      return;
    }
    const parent = getParentInDict(
      data.word.value,
      inlineDict.wordIndex,
      inlineDict.wordList
    );
    const parentOptions = parent.map((item) => {
      return {
        label: item,
        value: item,
      };
    });
    const findParent = parentOptions.find(
      (value) => value.value === data.real.value
    );
    if (findParent) {
      setParentOptions(parentOptions);
    } else {
      setParentOptions([
        ...parentOptions,
        { label: data.real.value, value: data.real.value },
      ]);
    }
  }, [inlineDict, data]);

  return (
    <AutoComplete
      disabled={readonly}
      options={parentOptions}
      value={data.parent?.value}
      onChange={(value: any, option: ValueType | ValueType[]) => {
        console.debug("wbw parent onChange", value);
        if (typeof onChange !== "undefined") {
          onChange(value);
        }
      }}
    >
      <Input disabled={readonly} allowClear placeholder="请输入" />
    </AutoComplete>
  );
};

export default WbwDetailParentWidget;
