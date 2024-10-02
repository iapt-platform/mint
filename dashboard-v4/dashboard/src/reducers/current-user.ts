import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export const ROLE_ROOT = "root";
export const ROLE_ADMINISTRATOR = "administrator";

export const TO_SIGN_IN = "/anonymous/users/sign-in";
export const TO_PROFILE = "/dashboard/users/logs";
export const TO_HOME = "/";

const KEY = "token";
export const DURATION = 60 * 60 * 24;

export const get = (): string | null => {
  let token = sessionStorage.getItem(KEY);
  if (token) {
    return token;
  }
  token = localStorage.getItem(KEY);
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

export interface IUser {
  id: string;
  nickName: string;
  realName: string;
  avatar: string;
  roles: string[] | null;
}

interface IState {
  payload?: IUser;
  guest?: boolean;
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
  },
});

export const { signIn, signOut, guest } = slice.actions;

export const isRoot = (state: RootState): boolean =>
  state.currentUser.payload?.roles?.includes(ROLE_ROOT) || false;
export const isAdministrator = (state: RootState): boolean =>
  state.currentUser.payload?.roles?.includes(ROLE_ADMINISTRATOR) || false;
export const currentUser = (state: RootState): IUser | undefined =>
  state.currentUser.payload;
export const isGuest = (state: RootState): boolean | undefined =>
  state.currentUser.guest;

export default slice.reducer;
