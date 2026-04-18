import type { IMeaning } from "./NissayaMeaning";

export const nissayaBase = (item: string, endings: string[]): IMeaning => {
  let word = item
    .trim()
    .replaceAll("။", "")
    .replaceAll("[}", "")
    .replaceAll("]", "")
    .replaceAll("(", "")
    .replaceAll(")", "")
    .replaceAll("၊", "")
    .replaceAll(",", "")
    .replaceAll(".", "");

  const end: string[] = [];
  for (let loop = 0; loop < 3; loop++) {
    for (let i = 0; i < word.length; i++) {
      const ending = word.slice(i);
      if (endings?.includes(ending)) {
        end.unshift(word.slice(i));
        word = word.slice(0, i);
      }
    }
  }
  return {
    base: word,
    ending: end,
  };
};
