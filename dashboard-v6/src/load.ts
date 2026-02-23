//import { Empty } from "google-protobuf/google/protobuf/empty_pb";
//import { Duration } from "google-protobuf/google/protobuf/duration_pb";

import {
  get as getToken,
  guest,
  type IUser,
  signIn,
  studioSignIn,
} from "./reducers/current-user";
//import { DURATION } from "./reducers/current-user";
import { type ISite, refresh as refreshLayout } from "./reducers/layout";
import {
  type ISettingItem,
  refresh as refreshSetting,
} from "./reducers/setting";
import { refresh as refreshTheme } from "./reducers/theme";
import { get } from "./request";
import { get as getLang } from "./locales";

import store from "./store";
import { grammar, type ITerm, update } from "./reducers/term-vocabulary";
import { push as nissayaEndingPush } from "./reducers/nissaya-ending-vocabulary";

import { pushRelation } from "./reducers/relation";
import type { IGroupMemberListResponse } from "./api/Group";

import type { IAiModel } from "./api/ai";
import type { IStudio } from "./api/Auth";
import type { IRelation, IRelationListResponse } from "./api/relation";

export interface ISettingModels {
  wbw?: IAiModel[];
  chat?: IAiModel[];
}
export interface ISiteInfoResponse {
  logo: string;
  title: string;
  subhead: string;
  models?: ISettingModels;
}
interface IUserData {
  id: string;
  nickName: string;
  realName: string;
  avatar: string;
  roles: string[];
  token: string;
}
export interface ITokenRefreshResponse {
  ok: boolean;
  message: string;
  data: IUserData;
}

interface ITermResponse {
  ok: boolean;
  message: string;
  data: {
    rows: ITerm[];
    count: number;
  };
}
interface INissayaEnding {
  ending: string;
}
interface INissayaEndingResponse {
  ok: boolean;
  message: string;
  data: {
    rows: INissayaEnding[];
    count: number;
  };
}

export const grammarTermFetch = () => {
  //获取语法术语表
  get<ITermResponse>(
    `/api/v2/term-vocabulary?view=grammar&lang=` + getLang()
  ).then((json) => {
    if (json.ok) {
      console.debug("grammar dispatch", json.data.rows);
      store.dispatch(grammar(json.data.rows));
    }
  });
};

const init = () => {
  get<ISite>("/api/v2/site-info/en").then((response) => {
    if ("title" in response) {
      const it: ISite = response;
      store.dispatch(refreshLayout(it));
    }
  });
  //获取用户登录信息
  const token = getToken();
  if (token) {
    get<ITokenRefreshResponse>("/api/v2/auth/current").then((response) => {
      console.log("auth", response);
      if (response.ok) {
        const it: IUser = {
          id: response.data.id,
          nickName: response.data.nickName,
          realName: response.data.realName,
          avatar: response.data.avatar,
          roles: response.data.roles,
        };
        store.dispatch(signIn([it, response.data.token]));
      } else {
        localStorage.removeItem("token");
        store.dispatch(guest(true));
      }
    });

    get<IGroupMemberListResponse>("/v2/group-member?view=user").then(
      (response) => {
        console.log("auth", response);
        if (response.ok) {
          const it: IStudio[] = response.data.rows.map((item) => {
            return item.group;
          });
          store.dispatch(studioSignIn(it));
        }
      }
    );
  } else {
    console.log("no token");
    store.dispatch(guest(true));
  }

  //获取用户设置
  const setting = localStorage.getItem("user-settings");
  if (setting !== null) {
    const json: ISettingItem[] = JSON.parse(setting);
    store.dispatch(refreshSetting(json));
  }

  //获取语法术语表
  grammarTermFetch();

  //获取术语表
  get<ITermResponse>(
    `/api/v2/term-vocabulary?view=community&lang=` + getLang()
  ).then((json) => {
    if (json.ok) {
      store.dispatch(update(json.data.rows));
    }
  });

  //获取nissaya ending 表
  get<INissayaEndingResponse>(`/api/v2/nissaya-ending-vocabulary?lang=my`).then(
    (json) => {
      if (json.ok) {
        const nissayaEnding = json.data.rows.map((item) => item.ending);
        store.dispatch(nissayaEndingPush(nissayaEnding));
      }
    }
  );

  //获取 relation 表
  const urlRelation = `/api/v2/relation?vocabulary=true&limit=1000`;
  console.debug("relations api request", urlRelation);
  get<IRelationListResponse>(urlRelation).then((json) => {
    console.debug("relations api response", json);
    if (json.ok) {
      const items: IRelation[] = json.data.rows.map((item) => {
        return {
          id: item.id,
          name: item.name,
          case: item.case,
          from: item.from,
          to: item.to,
        };
      });
      store.dispatch(pushRelation(items));
    }
  });

  //获取用户选择的主题
  const theme = localStorage.getItem("theme");
  if (theme === "dark") {
    store.dispatch(refreshTheme("dark"));
  } else {
    store.dispatch(refreshTheme("ant"));
  }

  //设置时区到cookie
  function setCookie(c_name: string, value: string, expiredays: number) {
    const exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie =
      c_name +
      "=" +
      escape(value) +
      (expiredays == null
        ? ""
        : "; expires=" + exdate.toUTCString() + ";path=/");
  }
  const date = new Date();
  const timezone = date.getTimezoneOffset();
  setCookie("timezone", timezone.toString(), 10);
};

export default init;
