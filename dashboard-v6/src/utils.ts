import type { SortOrder } from "antd/lib/table/interface";
import lodash from "lodash";

export function dashboardBasePath(): string {
  if (import.meta.env.BASE_URL.includes("http")) {
    //for CDN
    return import.meta.env.BASE_URL;
  } else {
    return window.location.origin + import.meta.env.BASE_URL;
  }
}

export function fullUrl(url: string): string {
  if (import.meta.env.BASE_URL.includes("http")) {
    //for CDN
    return import.meta.env.BASE_URL + url;
  } else {
    return window.location.origin + import.meta.env.BASE_URL + url;
  }
}

export function PaliToEn(pali: string): string {
  let output: string = pali.toLowerCase();
  output = output.replaceAll("ā", "a");
  output = output.replaceAll("ī", "i");
  output = output.replaceAll("ū", "u");
  output = output.replaceAll("ḍ", "d");
  output = output.replaceAll("ṭ", "t");
  output = output.replaceAll("ḷ", "l");
  output = output.replaceAll("ṅ", "n");
  output = output.replaceAll("ṇ", "n");
  output = output.replaceAll("ñ", "n");
  output = output.replaceAll("ṃ", "m");
  return output;
}

export function PaliReal(inStr: string | undefined | null): string {
  if (typeof inStr !== "string") {
    return "";
  }
  const paliLetter = "abcdefghijklmnoprstuvyāīūṅñṭḍṇḷṃ";
  let output: string = "";
  inStr = inStr.toLowerCase();
  inStr = inStr.replace(/ṁ/g, "ṃ");
  inStr = inStr.replace(/ŋ/g, "ṃ");
  for (const iterator of inStr) {
    if (paliLetter.includes(iterator)) {
      output += iterator;
    }
  }
  return output;
}

export const getSorterUrl = (sorter?: Record<string, SortOrder>): string => {
  let url: string = "";
  for (const key in sorter) {
    if (Object.prototype.hasOwnProperty.call(sorter, key)) {
      const element = sorter[key];
      const dir = element === "ascend" ? "asc" : "desc";
      let orderby = key;
      if (orderby === "updatedAt") {
        orderby = "updated_at";
      }
      url = `&order=${orderby}&dir=${dir}`;
    }
  }
  return url;
};

export const convertToPlain = (html: string): string => {
  // Create a new div element
  const tempDivElement = document.createElement("div");

  // Set the HTML content with the given value
  tempDivElement.innerHTML = html;

  // Retrieve the text property of the element
  return tempDivElement.textContent || tempDivElement.innerText || "";
};

export const randomString = (): string => {
  return lodash.times(20, () => lodash.random(35).toString(36)).join("");
};
/**
 * 10进制数字转为16进制字符串
 * @param {number} arg
 * @returns
 */
/*
作者：sq800
链接：https://juejin.cn/post/7250029395024281656
来源：稀土掘金
著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。
*/
export const numToHex = (arg: number) => {
  try {
    const a = arg.toString(16).toUpperCase();
    return a.length % 2 === 1 ? "0" + a : a;
  } catch (e) {
    console.warn("数字转16进制出错:", e);
  }
};
