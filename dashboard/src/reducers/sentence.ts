import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

interface ISentence {
  id: string;
  origin?: string[];
  translation?: string[];
}
interface IState {
  sentence: ISentence[];
}

const initialState: IState = { sentence: [] };

export const slice = createSlice({
  name: "sentence",
  initialState,
  reducers: {
    push: (state, action: PayloadAction<ISentence>) => {
      if (
        state.sentence.filter((value) => value.id === action.payload.id)
          .length === 0
      ) {
        state.sentence.push(action.payload);
      }
    },
  },
});

export const { push } = slice.actions;

export const sentence = (state: RootState): IState => state.sentence;

export const sentenceList = (state: RootState): ISentence[] =>
  state.sentence.sentence;

export default slice.reducer;
