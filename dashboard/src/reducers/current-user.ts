import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export const ROLE_ROOT = "root";
export const ROLE_ADMINISTRATOR = "administrator";

export const TO_SIGN_IN = "/anonymous/users/sign-in";
export const TO_PROFILE = "/dashboard/users/logs";

const KEY = "token";
export const DURATION = 60 * 60 * 24;

export const get = (): string | null => {
  return sessionStorage.getItem(KEY);
};

const set = (token: string) => {
  sessionStorage.setItem(KEY, token);
};

const remove = () => {
  sessionStorage.removeItem(KEY);
};

export interface IUser {
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
