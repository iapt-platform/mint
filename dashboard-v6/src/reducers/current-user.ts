import { createSlice, type PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import type { IStudio } from "../api/Auth";

export const ROLE_ROOT = "root";
export const ROLE_ADMINISTRATOR = "administrator";

export const TO_SIGN_IN = "/anonymous/sign-in";
export const TO_PROFILE = "/dashboard/users/logs";
export const TO_HOME = "/";
export const TO_WORKSPACE = "/workspace";

const KEY = "token";
/** 当前团队空间（一个 group）。只存在 localStorage，user token 不变 */
const TEAMSPACE_KEY = "teamspace";
export const DURATION = 60 * 60 * 24;

export const get = (): string | null => {
  let token = sessionStorage.getItem(KEY);
  if (token) {
    return token;
  }
  token = localStorage.getItem(KEY);
  console.debug("token", KEY, token);
  if (token) {
    return token;
  }
  return null;
};

const set = (token: string) => {
  sessionStorage.setItem(KEY, token);
  localStorage.setItem(KEY, token);
};

const remove = () => {
  sessionStorage.removeItem(KEY);
  localStorage.removeItem(KEY);
};

const removeTeamspace = () => {
  localStorage.removeItem(TEAMSPACE_KEY);
};

const saveTeamspace = (studio: IStudio) => {
  localStorage.setItem(TEAMSPACE_KEY, JSON.stringify(studio));
};

/**
 * 启动时先按 localStorage 恢复团队空间，避免页面先以个人身份取一次数据；
 * 成员资格等 group 列表回来后由 recallCurrStudio 校验。
 */
const loadTeamspace = (): IStudio | undefined => {
  try {
    const raw = localStorage.getItem(TEAMSPACE_KEY);
    const studio = raw ? (JSON.parse(raw) as IStudio) : undefined;
    return studio?.id ? studio : undefined;
  } catch {
    removeTeamspace();
    return undefined;
  }
};

export interface IUser {
  id: string;
  nickName: string;
  realName: string;
  avatar: string;
  roles: string[] | null;
}

interface IState {
  payload?: IUser;
  studio?: IStudio[];
  guest?: boolean;
  /** 当前团队空间；undefined 表示个人空间 */
  currStudio?: IStudio;
}

const initialState: IState = { currStudio: loadTeamspace() };

export const slice = createSlice({
  name: "current-user",
  initialState,
  reducers: {
    signIn: (state, action: PayloadAction<[IUser, string]>) => {
      state.payload = action.payload[0];
      set(action.payload[1]);
      state.guest = false;
    },
    signOut: (state) => {
      state.payload = undefined;
      state.guest = undefined;
      state.currStudio = undefined;
      remove();
      removeTeamspace();
    },
    guest: (state, action: PayloadAction<boolean>) => {
      state.guest = action.payload;
    },
    studioSignIn: (state, action: PayloadAction<IStudio[]>) => {
      state.studio = action.payload;
    },
    /** 切换到团队空间 */
    setCurrStudio: (state, action: PayloadAction<IStudio>) => {
      state.currStudio = action.payload;
      saveTeamspace(action.payload);
    },
    /** 切回个人空间 */
    studioSignOut: (state) => {
      state.currStudio = undefined;
      removeTeamspace();
    },
    /** group 列表到手后校验：已不在该 group 里就回到个人空间，否则用最新的 group 信息 */
    recallCurrStudio: (state) => {
      const id = state.currStudio?.id;
      if (!id || !state.studio) {
        return;
      }
      const studio = state.studio.find((value) => value.id === id);
      if (studio) {
        state.currStudio = studio;
        saveTeamspace(studio);
      } else {
        state.currStudio = undefined;
        removeTeamspace();
      }
    },
  },
});

export const {
  signIn,
  signOut,
  guest,
  studioSignIn,
  setCurrStudio,
  studioSignOut,
  recallCurrStudio,
} = slice.actions;

export const isRoot = (state: RootState): boolean =>
  state.currentUser.payload?.roles?.includes(ROLE_ROOT) || false;
export const isAdministrator = (state: RootState): boolean =>
  state.currentUser.payload?.roles?.includes(ROLE_ADMINISTRATOR) || false;
export const currentUser = (state: RootState): IUser | undefined =>
  state.currentUser.payload;
export const isGuest = (state: RootState): boolean | undefined =>
  state.currentUser.guest;
export const currentStudio = (state: RootState): IStudio | undefined =>
  state.currentUser.currStudio;

export const studioList = (state: RootState): IStudio[] | undefined =>
  state.currentUser.studio;
export default slice.reducer;
