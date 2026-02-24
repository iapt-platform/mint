import type { IntlShape } from "react-intl";
import type { MenuProps } from "antd";
import type { IApiResponseDictData } from "../../api/Dict";
import type { IWbw, TFieldName } from "../../types/wbw";

export const caseInDict = (
  wordIn: string,
  wordIndex: string[],
  wordList: IApiResponseDictData[],
  intl: IntlShape
): MenuProps["items"] => {
  if (!wordIn) return [];

  if (wordIndex.includes(wordIn)) {
    const result = wordList.filter((w) => w.word === wordIn);

    const myMap = new Map<string, number>();
    const factors: string[] = [];

    for (const item of result) {
      myMap.set(item.type + "#" + item.grammar, 1);
    }

    myMap.forEach((_value, key) => {
      factors.push(key);
    });

    const menu = factors.map((item) => {
      const arr = item.replaceAll(".", "").replaceAll("#", "$").split("$");

      const noNull = arr.filter(Boolean);

      noNull.forEach((v, i) => {
        noNull[i] = intl.formatMessage({
          id: `dict.fields.type.${v}.short.label`,
          defaultMessage: v,
        });
      });

      return { key: item, label: noNull.join(" ") };
    });

    return menu.length ? menu : [{ key: "", disabled: true, label: "Empty" }];
  }

  return [{ key: "", disabled: true, label: "Loading" }];
};

export const bookMarkColor = ["#fff", "#f99", "#ff9", "#9f9", "#9ff", "#99f"];

export const getParentInDict = (
  wordIn: string,
  wordIndex: string[],
  wordList: IApiResponseDictData[]
): string[] => {
  if (wordIndex.includes(wordIn)) {
    const result = wordList.filter((word) => word.word === wordIn);
    //查重
    //TODO 加入信心指数并排序
    const myMap = new Map<string, number>();
    const parent: string[] = [];
    for (const iterator of result) {
      if (iterator.parent) {
        myMap.set(iterator.parent, 1);
      }
    }
    myMap.forEach((_value, key) => {
      parent.push(key);
    });
    return parent;
  } else {
    return [];
  }
};

export const getFactorsInDict = (
  wordIn: string,
  wordIndex: string[],
  wordList: IApiResponseDictData[]
): string[] => {
  if (wordIndex.includes(wordIn)) {
    const result = wordList.filter((word) => word.word === wordIn);
    //查重
    //TODO 加入信心指数并排序
    const myMap = new Map<string, number>();
    const factors: string[] = [];
    for (const iterator of result) {
      if (iterator.factors) {
        myMap.set(iterator.factors, 1);
      }
    }
    myMap.forEach((_value, key) => {
      factors.push(key);
    });
    return factors;
  } else {
    return [];
  }
};

export const errorClass = (
  field: TFieldName,
  data?: string | null,
  answer?: string | null
): string => {
  let classError = "";

  if (answer !== data) {
    classError = " wbw_check";
    switch (field) {
      case "parent":
        classError += " wbw_error";
        break;
      case "case":
        classError += " wbw_error";
        break;
      case "factors":
        classError += " wbw_warning";
        break;
      case "factorMeaning":
        classError += " wbw_info";
        break;
      case "meaning":
        classError += " wbw_info";
        break;
    }
  }

  return classError;
};

export const relationWordId = (word: IWbw) => {
  return `${word.book}-${word.para}-` + word.sn.join("-");
};
