import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import { TCourseRole } from "../components/api/Course";

export const ROLE_ROOT = "root";
export const ROLE_ASSISTANT = "assistant";

const KEY = "token";
export const DURATION = 60 * 60 * 24;

export const get = (): string | null => {
  const token = sessionStorage.getItem(KEY);
  if (token) {
    return token;
  }

  return null;
};

const remove = () => {
  sessionStorage.removeItem(KEY);
  localStorage.removeItem(KEY);
};

export interface ICourseUser {
  channelId?: string | null;
  role: TCourseRole;
}

interface IState {
  payload?: ICourseUser;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "course-user",
  initialState,
  reducers: {
    signIn: (state, action: PayloadAction<ICourseUser>) => {
      state.payload = action.payload;
    },
    signOut: (state) => {
      state.payload = undefined;
      remove();
    },
  },
});

export const { signIn, signOut } = slice.actions;

export const isRoot = (state: RootState): boolean =>
  state.courseUser.payload?.role.includes(ROLE_ROOT) || false;
export const isAssistant = (state: RootState): boolean =>
  state.courseUser.payload?.role.includes(ROLE_ASSISTANT) || false;
export const courseUser = (state: RootState): ICourseUser | undefined =>
  state.courseUser.payload;

export default slice.reducer;
