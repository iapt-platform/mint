import type { IntlShape } from "react-intl";
import type { MenuProps } from "antd";
import type { IApiResponseDictData } from "../../api/dict";
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

// ============ 优化工具函数 ============

// 优化1: 使用 Map 缓存 sn 索引,提升查找性能从 O(n) 到 O(1)
export const createSnIndexMap = (data: IWbw[]): Map<string, IWbw> => {
  const map = new Map<string, IWbw>();
  data.forEach((item) => {
    map.set(item.sn.join(), item);
  });
  return map;
};

// 优化2: 缓存字符串拼接结果
export const createSnKey = (sn: number[]): string => sn.join();

// 优化3: 提取 paraMark 为纯函数,便于 memoization
export const paraMark = (wbwData: IWbw[]): IWbw[] => {
  if (!wbwData || wbwData.length === 0) return wbwData;

  let start = false;
  let bookCode = "";
  let count = 0;
  let bookCodeStack: string[] = [];

  // 使用浅拷贝而非深拷贝
  const result = [...wbwData];

  result.forEach((value: IWbw, index: number) => {
    if (value.word.value === "(") {
      start = true;
      bookCode = "";
      bookCodeStack = [];
      return;
    }
    if (start) {
      if (!isNaN(Number(value.word.value.replaceAll("-", "")))) {
        if (bookCode === "" && bookCodeStack.length > 0) {
          bookCode = bookCodeStack[0];
        }
        const dot = bookCode.lastIndexOf(".");
        let bookName = "";
        if (dot === -1) {
          bookName = bookCode;
        } else {
          bookName = bookCode.substring(0, dot + 1);
        }
        bookName = bookName.substring(0, 64).toLowerCase();
        if (!bookCodeStack.includes(bookName)) {
          bookCodeStack.push(bookName);
        }
        if (bookName !== "") {
          result[index] = { ...result[index], bookName };
          count++;
        }
      } else if (value.word.value === ";") {
        bookCode = "";
        return;
      } else if (value.word.value === ")") {
        start = false;
        return;
      }
      bookCode += value.word.value;
    }
  });

  if (count > 0) {
    console.debug("para mark", count);
  }
  return result;
};
// 优化4: 提取进度计算为纯函数
export const getWbwProgress = (data: IWbw[], answer?: IWbw[]): number => {
  const allWord = data.filter(
    (value) =>
      value.real.value &&
      value.real.value?.length > 0 &&
      value.type?.value !== ".ctl."
  );

  if (allWord.length === 0) return 0;

  let final: IWbw[];
  if (answer) {
    // 使用 Map 优化查找
    const answerMap = createSnIndexMap(answer);

    final = allWord.filter((value: IWbw) => {
      const snKey = createSnKey(value.sn);
      const currAnswer = answerMap.get(snKey);

      if (!currAnswer) return false;

      const checks = [
        ["meaning", currAnswer.meaning?.value, value.meaning?.value],
        ["factors", currAnswer.factors?.value, value.factors?.value],
        [
          "factorMeaning",
          currAnswer.factorMeaning?.value,
          value.factorMeaning?.value,
        ],
        ["case", currAnswer.case?.value, value.case?.value],
        ["parent", currAnswer.parent?.value, value.parent?.value],
      ];

      return checks.every(([value, answerVal, valueVal]) => {
        //TODO remove value
        console.debug("checks", value);
        if (!answerVal) return true;
        return valueVal && valueVal.trim().length > 0;
      });
    });
  } else {
    final = allWord.filter(
      (value) =>
        value.meaning?.value &&
        value.factors?.value &&
        value.factorMeaning?.value &&
        value.case?.value
    );
  }

  const finalLen = final.reduce(
    (sum, v) => sum + (v.real.value?.length || 0),
    0
  );
  const allLen = allWord.reduce(
    (sum, v) => sum + (v.real.value?.length || 0),
    0
  );

  return allLen > 0 ? Math.round((finalLen * 100) / allLen) : 0;
};
