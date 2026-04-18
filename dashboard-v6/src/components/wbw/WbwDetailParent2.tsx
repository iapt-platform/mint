import { AutoComplete, Form, Input, Select } from "antd";
import { useMemo } from "react"; // 替换 useEffect, useState
import { useIntl } from "react-intl";
import { useAppSelector } from "../../hooks";

import type { IWbw, IWbwField } from "../../types/wbw";
import { inlineDict as _inlineDict } from "../../reducers/inline-dict";

interface IWidget {
  data: IWbw;
  onChange?: (value: IWbwField) => void;
}

const WbwParent2Widget = ({ data, onChange }: IWidget) => {
  const intl = useIntl();
  const inlineDict = useAppSelector(_inlineDict);

  // 1. 将计算逻辑封装，并用 useMemo 缓存结果
  const parentOptions = useMemo(() => {
    const wordIn = data.parent?.value;

    // 基础校验：如果没有输入值，直接返回空
    if (typeof wordIn !== "string" || !wordIn) {
      return [];
    }

    const { wordIndex, wordList } = inlineDict;

    if (wordIndex.includes(wordIn)) {
      // 过滤并提取 parent 字段
      const results = wordList.filter((word) => word.word === wordIn);

      // 使用 Set 进行去重（比 Map 更简洁）
      const parentSet = new Set<string>();
      results.forEach((item) => {
        if (item.parent) {
          parentSet.add(item.parent);
        }
      });

      // 转换为 AntD 要求的 Options 格式
      return Array.from(parentSet).map((p) => ({
        label: p,
        value: p,
      }));
    }

    return [];
  }, [inlineDict, data.parent?.value]); // 仅在字典或输入值改变时重新计算

  // 2. 语法选项同样可以使用 useMemo 优化（避免每次 render 都重新创建数组对象）
  const grammarOptions = useMemo(() => {
    const grammar = ["prp", "pp", "fpp", "pass", "caus", "vdn"];
    return grammar.map((item) => ({
      value: `.${item}.`,
      label: intl.formatMessage({
        id: `dict.fields.type.${item}.label`,
        defaultMessage: item,
      }),
    }));
  }, [intl]);

  return (
    <div style={{ display: "flex", gap: "8px" }}>
      <Form.Item
        name="parent2"
        label={intl.formatMessage({ id: "forms.fields.parent2.label" })}
        tooltip={intl.formatMessage({ id: "forms.fields.parent2.tooltip" })}
        style={{ flex: 1, marginBottom: 0 }}
      >
        <AutoComplete
          options={parentOptions}
          onChange={(value: string) => {
            onChange?.({ field: "parent2", value: value });
          }}
        >
          <Input allowClear placeholder="请输入" />
        </AutoComplete>
      </Form.Item>

      <Form.Item name="grammar2" noStyle>
        <Select
          style={{ width: 120 }}
          allowClear
          options={grammarOptions}
          placeholder="语法"
          onChange={(value: string) => {
            onChange?.({ field: "grammar2", value: value });
          }}
        />
      </Form.Item>
    </div>
  );
};

export default WbwParent2Widget;
