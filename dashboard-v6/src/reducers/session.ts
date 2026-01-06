import { createSlice, type PayloadAction } from "@reduxjs/toolkit";
import * as jose from "jose";

import type { RootState } from "../store";

export const SIGN_IN = "/anonymous/sign-in";
export const PERSONAL = "/dashboard/personal";

export interface ISignIn {
  token: string;
  roles: string[];
}

const KEY = "token";
export const get = (): string | null => {
  return sessionStorage.getItem(KEY);
};

const set = (token: string) => {
  sessionStorage.setItem(KEY, token);
};

const remove = () => {
  sessionStorage.removeItem(KEY);
};

interface SessionState {
  name?: string;
  roles: string[];
}

const initialState: SessionState = { roles: [] };

export const sessionSlice = createSlice({
  name: "session",
  initialState,
  reducers: {
    signOut: (state) => {
      state.name = undefined;
      state.roles = [];
      remove();
    },
    signIn: (state, action: PayloadAction<ISignIn>) => {
      try {
        const claims = jose.decodeJwt(action.payload.token);
        if (claims.sub) {
          state.name = claims.sub;
          state.roles = action.payload.roles;
        }
        set(action.payload.token);
      } catch (e) {
        console.error(e);
        state.name = undefined;
      }
    },
  },
});

export const { signIn, signOut } = sessionSlice.actions;

export const currentUser = (state: RootState) => state.session.name;

export default sessionSlice.reducer;
