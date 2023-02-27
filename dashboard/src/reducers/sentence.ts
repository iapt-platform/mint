import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

interface IState {
  sentence: string[];
}

const initialState: IState = { sentence: [] };

export const slice = createSlice({
  name: "sentence",
  initialState,
  reducers: {
    push: (state, action: PayloadAction<string>) => {
      if (state.sentence.includes(action.payload) === false) {
        state.sentence.push(action.payload);
      }
    },
  },
});

export const { push } = slice.actions;

export const sentence = (state: RootState): IState => state.sentence;

export const sentenceList = (state: RootState): string[] =>
  state.sentence.sentence;

export default slice.reducer;
