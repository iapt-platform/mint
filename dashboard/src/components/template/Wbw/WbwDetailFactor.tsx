import { AutoComplete, Input } from "antd";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../../hooks";

import {
  add,
  inlineDict as _inlineDict,
  wordIndex,
} from "../../../reducers/inline-dict";
import { get } from "../../../request";
import store from "../../../store";
import { IApiResponseDictList } from "../../api/Dict";
import { getFactorsInDict } from "./WbwFactors";
import { IWbw } from "./WbwWord";

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
const WbwDetailFactorWidget = ({
  data,
  readonly = false,
  onChange,
}: IWidget) => {
  const [factorOptions, setFactorOptions] = useState<ValueType[]>([]);
  const inlineDict = useAppSelector(_inlineDict);
  const inlineWordIndex = useAppSelector(wordIndex);

  const lookup = (words: string[]) => {
    //查询这个词在内存字典里是否有
    let search: string[] = [];
    for (const it of words) {
      if (!inlineWordIndex.includes(it)) {
        search.push(it);
      }
    }
    if (search.length === 0) {
      return;
    }
    get<IApiResponseDictList>(`/v2/wbwlookup?base=${search}`).then((json) => {
      console.log("lookup ok", json.data.count);
      store.dispatch(add(json.data.rows));
    });
  };

  useEffect(() => {
    if (
      typeof data.factors === "undefined" ||
      typeof data.factors.value !== "string"
    ) {
      return;
    }
    lookup(data.factors?.value.replaceAll("-", "+").split("+"));
  }, [data.factors]);

  useEffect(() => {
    if (!data.real.value) {
      return;
    }
    const factors = getFactorsInDict(
      data.real.value,
      inlineDict.wordIndex,
      inlineDict.wordList
    );
    const options = factors.map((item) => {
      return {
        label: item,
        value: item,
      };
    });
    setFactorOptions([
      ...options,
      { label: data.real.value, value: data.real.value },
    ]);
  }, [data.real.value, inlineDict.wordIndex, inlineDict.wordList]);

  return (
    <AutoComplete
      disabled={readonly}
      options={factorOptions}
      value={data.factors?.value}
      onChange={(value: string) => {
        if (typeof onChange !== "undefined") {
          onChange(value);
        }
      }}
    >
      <Input disabled={readonly} placeholder="请输入" allowClear />
    </AutoComplete>
  );
};

export default WbwDetailFactorWidget;
