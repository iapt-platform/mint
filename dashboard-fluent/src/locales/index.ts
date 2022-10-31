import Cookies from "js-cookie";

import "moment/locale/zh-cn";
import "moment/locale/zh-tw";
import "moment/locale/es";
import "moment/locale/fr";
import "moment/locale/ja";
import "moment/locale/ko";

import languages from "./languages";
import enUS from "./en-US";
import zhHans from "./zh-Hans";
import zhHant from "./zh-Hant";

const KEY = "locale";

export const DEFAULT: string = process.env.REACT_APP_DEFAULT_LOCALE || "zh-Hans";
export const LANGUAGES: string[] = process.env.REACT_APP_LANGUAGES?.split(",") || ["en-US", "zh-Hans"];

export const get = (): string => {
	return localStorage.getItem(KEY) || Cookies.get(KEY) || DEFAULT;
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

const messages = (lang: string): ILocale => {
	switch (lang) {
		case "en-US":
			return {
				messages: { ...enUS, ...languages },
			};
		case "zh-Hant":
			return {
				messages: { ...zhHant, ...languages },
			};
		default:
			return {
				messages: { ...zhHans, ...languages },
			};
	}
};

export default messages;
