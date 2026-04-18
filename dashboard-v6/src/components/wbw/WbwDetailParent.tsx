import { AutoComplete, Input } from "antd";
import { useMemo } from "react"; // 替换 useEffect, useState
import { useAppSelector } from "../../hooks";
import { inlineDict as _inlineDict } from "../../reducers/inline-dict";
import type { IWbw } from "../../types/wbw";
import { getParentInDict } from "./utils";

interface IWidget {
  data: IWbw;
  readonly?: boolean;
  onChange?: (value: string) => void;
}

const WbwDetailParentWidget = ({
  data,
  readonly = false,
  onChange,
}: IWidget) => {
  const inlineDict = useAppSelector(_inlineDict);

  // 使用 useMemo 代替 useState + useEffect
  const parentOptions = useMemo(() => {
    // 基础校验：如果没有值，返回空数组
    if (!data.real.value) {
      return [];
    }

    // 从字典获取父级选项
    const parentsFromDict = getParentInDict(
      data.word.value,
      inlineDict.wordIndex,
      inlineDict.wordList
    );

    const options = parentsFromDict.map((item) => ({
      label: item,
      value: item,
    }));

    // 检查当前值是否已在选项中
    const exists = options.some((opt) => opt.value === data.real.value);

    if (exists) {
      return options;
    } else {
      // 如果不在，手动添加当前值作为选项之一
      return [...options, { label: data.real.value, value: data.real.value }];
    }
  }, [inlineDict, data.word.value, data.real.value]); // 仅在依赖项变化时重新计算

  return (
    <AutoComplete
      disabled={readonly}
      options={parentOptions}
      value={data.parent?.value}
      onChange={(value: string) => {
        console.debug("wbw parent onChange", value);
        if (onChange) {
          onChange(value);
        }
      }}
    >
      <Input disabled={readonly} allowClear placeholder="请输入" />
    </AutoComplete>
  );
};

export default WbwDetailParentWidget;
