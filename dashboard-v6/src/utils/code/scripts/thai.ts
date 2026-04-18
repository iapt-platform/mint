import { buildConverter } from "../core/buildConverter";

const romanToLocalMap = {
  k: "ก",
  a: "อ",
} as const;

const localToRomanMap = {
  ก: "k",
  อ: "a",
} as const;

export const thai = {
  fromRoman: buildConverter(romanToLocalMap),
  toRoman: buildConverter(localToRomanMap),
};
