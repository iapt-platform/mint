import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export const ROLE_ROOT = "root";
export const ROLE_ADMINISTRATOR = "administrator";

export const TO_SIGN_IN = "/anonymous/users/sign-in";
export const TO_PROFILE = "/dashboard/users/logs";
export const TO_HOME = "/";

const KEY = "token";
export const DURATION = 60 * 60 * 24;

const IS_LOCAL_ENABLE = process.env.REACT_APP_ENABLE_LOCAL_TOKEN === "true";

export const get = (): string | null => {
  const token = sessionStorage.getItem(KEY);
  if (token) {
    return token;
  }
  if (IS_LOCAL_ENABLE) {
    return localStorage.getItem(KEY);
  }
  return null;
};

const set = (token: string) => {
  sessionStorage.setItem(KEY, token);
  if (IS_LOCAL_ENABLE) {
    localStorage.setItem(KEY, token);
  }
};

const remove = () => {
  sessionStorage.removeItem(KEY);
  localStorage.removeItem(KEY);
};

export interface IUser {
  id: string;
  nickName: string;
  realName: string;
  avatar: string;
  roles: string[];
}

interface IState {
  payload?: IUser;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "current-user",
  initialState,
  reducers: {
    signIn: (state, action: PayloadAction<[IUser, string]>) => {
      state.payload = action.payload[0];
      set(action.payload[1]);
    },
    signOut: (state) => {
      state.payload = undefined;
      remove();
    },
  },
});

export const { signIn, signOut } = slice.actions;

export const isRoot = (state: RootState): boolean =>
  state.currentUser.payload?.roles.includes(ROLE_ROOT) || false;
export const isAdministrator = (state: RootState): boolean =>
  state.currentUser.payload?.roles.includes(ROLE_ADMINISTRATOR) || false;
export const currentUser = (state: RootState): IUser | undefined =>
  state.currentUser.payload;

export default slice.reducer;
