import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export type TTheme = "dark" | "ant" | undefined;

interface IState {
  theme?: string;
}

const initialState: IState = { theme: "ant" };

export const slice = createSlice({
  name: "theme",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<string>) => {
      state.theme = action.payload;
    },
  },
});

export const { refresh } = slice.actions;

export const currTheme = (state: RootState): string | undefined =>
  state.theme.theme;

export default slice.reducer;
