import Cookies from "js-cookie";

import "moment/locale/zh-cn";
import "moment/locale/zh-tw";
import "moment/locale/es";
import "moment/locale/fr";
import "moment/locale/ja";
import "moment/locale/ko";

const KEY = "locale";

export const get = (): string => {
  return localStorage.getItem(KEY) || Cookies.get(KEY) || "en-US";
};

export const set = (lang: string, reload: boolean) => {
  Cookies.set(KEY, lang);
  localStorage.setItem(KEY, lang);
  if (reload) {
    window.location.reload();
  }
};

export const remove = () => {
  Cookies.remove(KEY);
  localStorage.removeItem(KEY);
};

interface ILocale {
  messages: Record<string, string>;
}
