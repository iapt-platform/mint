import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import { ITerm } from "../components/term/TermEdit";

interface IState {
  word?: ITerm | null;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "term-change",
  initialState,
  reducers: {
    click: (state, action: PayloadAction<ITerm | null>) => {
      state.word = action.payload;
    },
  },
});

export const { click } = slice.actions;

export const clickedTerm = (state: RootState): ITerm | null | undefined =>
  state.termClick.word;

export default slice.reducer;
