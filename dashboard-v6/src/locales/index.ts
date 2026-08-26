import Cookies from "js-cookie";
import { type MessageFormatElement } from "react-intl";
import dayjs from "dayjs";
import isLeapYear from "dayjs/plugin/isLeapYear";
import timezone from "dayjs/plugin/timezone";
import utc from "dayjs/plugin/utc";
import localizedFormat from "dayjs/plugin/localizedFormat";
import "dayjs/locale/zh-cn";
import "dayjs/locale/zh-tw";
import "dayjs/locale/en";

dayjs.extend(isLeapYear);
dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(localizedFormat);

import enUS from "./en-US";
import zhHans from "./zh-Hans";
import zhHant from "./zh-Hant";

const KEY = "locale";

export const DEFAULT: string =
  import.meta.env.VITE_APP_DEFAULT_LOCALE || "zh-Hans";

export const get = (): string => {
  return localStorage.getItem(KEY) || Cookies.get(KEY) || DEFAULT;
};

export const detect = (): string => get();

const applyDayjsLocale = (locale: string) => {
  switch (locale) {
    case "zh-Hans":
      dayjs.locale("zh-cn");
      break;
    case "zh-Hant":
      dayjs.locale("zh-tw");
      break;
    default:
      dayjs.locale("en-us");
      break;
  }
};

export const set = (locale: string) => {
  applyDayjsLocale(locale);
  localStorage.setItem(KEY, locale);
  Cookies.set(KEY, locale, { expires: 365, path: "/" });
};

// 首次加载时同步 dayjs 语言，避免刷新后时间等格式化仍为英文
applyDayjsLocale(get());

export const messages = (
  locale: string
): Record<string, string> | Record<string, MessageFormatElement[]> => {
  switch (locale) {
    case "zh-Hans":
      return zhHans;
    case "zh-Hant":
      return zhHant;
    default:
      return enUS;
  }
};
