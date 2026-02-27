/**
 *
 */
import { createSlice, type PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import type { ArticleMode } from "../api/Article";

interface IMode {
  id?: string;
  mode?: ArticleMode;
}

interface IState {
  id?: string;
  mode?: IMode;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "articleMode",
  initialState,
  reducers: {
    modeChange: (state, action: PayloadAction<IMode>) => {
      state.mode = action.payload;
      console.log("mode", action.payload);
    },
  },
});

export const { modeChange } = slice.actions;

export const mode = (state: RootState): IMode | undefined =>
  state.articleMode.mode;

export default slice.reducer;
