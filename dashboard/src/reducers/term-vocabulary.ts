/**
 * 从服务器获取的术语表
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface ITerm {
  word: string;
  meaning: string;
}

interface IState {
  term?: ITerm[];
}

const initialState: IState = {};

export const slice = createSlice({
  name: "term-vocabulary",
  initialState,
  reducers: {
    push: (state, action: PayloadAction<ITerm[]>) => {
      state.term = action.payload;
    },
  },
});

export const { push } = slice.actions;

export const getTerm = (state: RootState): ITerm[] | undefined =>
  state.termVocabulary.term;

export default slice.reducer;
