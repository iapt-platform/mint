import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { ITermDataResponse } from "../components/api/Term";
import { ITerm } from "../components/term/TermEdit";

import type { RootState } from "../store";

interface IState {
  word?: ITermDataResponse;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "term-change",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<ITermDataResponse>) => {
      state.word = action.payload;
    },
  },
});

export const { refresh } = slice.actions;

export const changedTerm = (state: RootState): ITermDataResponse | undefined =>
  state.termChange.word;

export default slice.reducer;
