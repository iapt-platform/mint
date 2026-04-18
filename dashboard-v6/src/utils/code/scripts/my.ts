import { buildConverter } from "../core/buildConverter";

const romanToLocalMap = {
  kh: "ခ္",
  gh: "ဃ္",
  k: "က္",
  a: "အ",
  ā: "အာ",
} as const;

const localToRomanMap = {
  ခ္: "kh",
  ဃ္: "gh",
  က္: "k",
  အ: "a",
  အာ: "ā",
} as const;

export const my = {
  fromRoman: buildConverter(romanToLocalMap),
  toRoman: buildConverter(localToRomanMap),
};
