import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

interface IState {
  id?: string;
  roles?: string[];
}

const initialState: IState = {};

export const slice = createSlice({
  name: "current-user",
  initialState,
  reducers: {
    signIn: (state, action: PayloadAction<string>) => {
      console.log(action.payload);
      state.id = "todo";
      state.roles = [];
    },
    signOut: (state) => {
      state = {};
    },
  },
});

export const { signIn, signOut } = slice.actions;

const ADMINISTRATOR = "administrator";

export const isSignIn = (state: RootState): boolean =>
  state.currentUser.id !== undefined;
export const isAdministrtor = (state: RootState): boolean =>
  hasRole(state, ADMINISTRATOR);

export const hasRole = (state: RootState, role: string): boolean =>
  state.currentUser.roles !== undefined &&
  state.currentUser.roles.includes(role);

export default slice.reducer;
