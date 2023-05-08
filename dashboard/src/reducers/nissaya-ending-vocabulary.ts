/**
 * 从服务器获取的术语表
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

interface IState {
  ending?: string[];
}

const initialState: IState = {};

export const slice = createSlice({
  name: "nissaya-ending-vocabulary",
  initialState,
  reducers: {
    push: (state, action: PayloadAction<string[]>) => {
      state.ending = action.payload;
    },
  },
});

export const { push } = slice.actions;

export const getEnding = (state: RootState): string[] | undefined =>
  state.nissayaEndingVocabulary.ending;

export default slice.reducer;
