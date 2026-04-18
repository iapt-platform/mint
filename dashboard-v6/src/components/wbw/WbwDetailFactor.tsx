import { AutoComplete, Input } from "antd";
import { useCallback, useEffect, useMemo } from "react";
import { useAppSelector } from "../../hooks";

import {
  add,
  inlineDict as _inlineDict,
  wordIndex,
} from "../../reducers/inline-dict";
import { get } from "../../request";
import store from "../../store";
import type { IApiResponseDictList } from "../../api/dict";

import type { IWbw } from "../../types/wbw";
import { getFactorsInDict } from "./utils";

interface ValueType {
  key?: string;
  label: React.ReactNode;
  value: string | number;
}
interface IWidget {
  data: IWbw;
  readonly?: boolean;
  onChange?: (value: string) => void;
}

const WbwDetailFactorWidget = ({
  data,
  readonly = false,
  onChange,
}: IWidget) => {
  const inlineDict = useAppSelector(_inlineDict);
  const inlineWordIndex = useAppSelector(wordIndex);

  // 1. Wrap lookup in useCallback to stabilize the reference
  const lookup = useCallback(
    (words: string[]) => {
      const search = words.filter((word) => !inlineWordIndex.includes(word));

      if (search.length === 0) return;

      get<IApiResponseDictList>(`/v2/wbwlookup?base=${search}`).then((json) => {
        console.log("lookup ok", json.data.count);
        store.dispatch(add(json.data.rows));
      });
    },
    [inlineWordIndex]
  );

  // 2. Effect for external API synchronization
  useEffect(() => {
    const factorValue = data.factors?.value;
    if (typeof factorValue !== "string") return;

    const words = factorValue.replaceAll("-", "+").split("+");
    lookup(words);
  }, [data.factors?.value, lookup]);

  // 3. Derived State: Calculate options during render instead of useEffect + setState
  const factorOptions = useMemo(() => {
    const realValue = data.real?.value;
    if (!realValue) return [];

    const factors = getFactorsInDict(
      realValue,
      inlineDict.wordIndex,
      inlineDict.wordList
    );

    const options: ValueType[] = factors.map((item) => ({
      label: item,
      value: item,
    }));

    return [...options, { label: realValue, value: realValue }];
  }, [data.real?.value, inlineDict.wordIndex, inlineDict.wordList]);

  return (
    <AutoComplete
      disabled={readonly}
      options={factorOptions}
      value={data.factors?.value ?? ""}
      onChange={(value: string) => onChange?.(value)}
    >
      <Input disabled={readonly} placeholder="请输入" allowClear />
    </AutoComplete>
  );
};

export default WbwDetailFactorWidget;
