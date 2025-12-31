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

export const detect = (): string => Cookies.get(KEY) || "en-US";

export const set = (locale: string) => {
  switch (locale) {
    case "zh-Hans":
      dayjs.locale("zh-cn");
      break;
    case "zh-Hants":
      dayjs.locale("zh-tw");
      break;
    default:
      dayjs.locale("en-us");
      break;
  }
  Cookies.set(KEY, locale);
};

export const messages = (
  locale: string
): Record<string, string> | Record<string, MessageFormatElement[]> => {
  switch (locale) {
    case "zh-Hans":
      return zhHans;
    case "zh-Hants":
      return zhHant;
    default:
      return enUS;
  }
};
