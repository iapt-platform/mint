import type { IGrammarRecent } from "./GrammarRecent";

const maxRecent = 10;
export const storeKey = "grammar-handbook/recent";

export const popRecent = (): IGrammarRecent | null => {
  const old = localStorage.getItem(storeKey);
  if (old) {
    const recentList = JSON.parse(old);
    const top = recentList.shift();
    localStorage.setItem(storeKey, JSON.stringify(recentList));
    return top;
  } else {
    return null;
  }
};

export const pushRecent = (value: IGrammarRecent) => {
  const old = localStorage.getItem(storeKey);
  if (old) {
    const newRecent = [value, ...JSON.parse(old)].slice(0, maxRecent - 1);
    localStorage.setItem(storeKey, JSON.stringify(newRecent));
  } else {
    localStorage.setItem(storeKey, JSON.stringify([value]));
  }
};
