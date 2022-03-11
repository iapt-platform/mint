import enUS from "./en-US";
import zhHans from "./zh-Hans";
import zhHant from "./zh-Hant";

const KEY = "locale";

export const get = (): string => {
  return localStorage.getItem(KEY) || "en-US";
};

export const set = (lang: string) => {
  localStorage.setItem(KEY, lang);
};

const languages = {
  "languages.en-US": "English",
  "languages.zh-Hans": "简体中文",
  "languages.zh-Hant": "繁體中文",
};

export const messages = (lang: string): any => {
  switch (lang) {
    case "zh-Hans":
      return { ...languages, ...zhHans };
    case "zh-Hant":
      return { ...languages, ...zhHant };
    default:
      return { ...languages, ...enUS };
  }
};
