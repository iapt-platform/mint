/**
 * 章节改变命令
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { ArticleType } from "../components/article/Article";

import type { RootState } from "../store";

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
