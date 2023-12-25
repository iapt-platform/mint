import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import { ITerm } from "../components/term/TermEdit";

interface IState {
  word?: ITerm;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "term-change",
  initialState,
  reducers: {
    click: (state, action: PayloadAction<ITerm>) => {
      state.word = action.payload;
    },
  },
});

export const { click } = slice.actions;

export const clickedTerm = (state: RootState): ITerm | undefined =>
  state.termClick.word;

export default slice.reducer;
