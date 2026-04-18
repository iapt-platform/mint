// src/reducers/theme.ts
import { createSlice, type PayloadAction } from "@reduxjs/toolkit";
import type { RootState } from "../store";

export type TThemeMode = "system" | "light" | "dark";

const initialState = {
  mode: (localStorage.getItem("theme/mode") as TThemeMode) ?? "system",
};

const themeSlice = createSlice({
  name: "theme",
  initialState,
  reducers: {
    themeChange: (state, action: PayloadAction<TThemeMode>) => {
      state.mode = action.payload;
    },
  },
});

export const { themeChange } = themeSlice.actions;
export const mode = (state: RootState) => state.theme.mode;
export default themeSlice.reducer;
