/**
 * 章节改变命令
 */
import { createSlice, type PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import type { ArticleType } from "../api/Corpus";

export interface IParam {
  book: number;
  para: number;
  wordStart?: number;
  wordEnd?: number;
  type: ArticleType;
}
interface IState {
  param?: IParam;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "para-change",
  initialState,
  reducers: {
    change: (state, action: PayloadAction<IParam>) => {
      state.param = action.payload;
      console.log("command", action.payload);
    },
  },
});

export const { change } = slice.actions;

export const paraParam = (state: RootState): IParam | undefined =>
  state.paraChange.param;

export default slice.reducer;
