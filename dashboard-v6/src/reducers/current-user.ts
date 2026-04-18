import { createSlice, type PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import type { IStudio } from "../api/Auth";

export const ROLE_ROOT = "root";
export const ROLE_ADMINISTRATOR = "administrator";

export const TO_SIGN_IN = "/anonymous/sign-in";
export const TO_PROFILE = "/dashboard/users/logs";
export const TO_HOME = "/";

const KEY = "token";
const STUDIO_KEY = "studio";
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

const removeStudio = () => {
  localStorage.removeItem(STUDIO_KEY);
};

const setStudio = (id: string) => {
  localStorage.setItem(STUDIO_KEY, id);
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
  currStudio?: IStudio;
}

const initialState: IState = {};

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
      remove();
    },
    guest: (state, action: PayloadAction<boolean>) => {
      state.guest = action.payload;
    },
    studioSignIn: (state, action: PayloadAction<IStudio[]>) => {
      state.studio = action.payload;
    },
    setCurrStudio: (state, action: PayloadAction<IStudio>) => {
      state.currStudio = action.payload;
      setStudio(action.payload.id);
    },
    studioSignOut: (state) => {
      state.currStudio = undefined;
      removeStudio();
    },
    recallCurrStudio: (state) => {
      const id = localStorage.getItem(STUDIO_KEY);
      if (id && state.studio) {
        state.currStudio = state.studio.find((value) => value.id);
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
