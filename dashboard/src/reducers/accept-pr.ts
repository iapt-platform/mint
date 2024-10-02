/**
 *
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { ISentence } from "../components/template/SentEdit";

import type { RootState } from "../store";

interface IState {
  sentences?: ISentence[];
  done: string[];
}

const initialState: IState = { done: [] };

export const slice = createSlice({
  name: "accept-pr",
  initialState,
  reducers: {
    accept: (state, action: PayloadAction<ISentence[]>) => {
      state.sentences = action.payload;
      state.done = [];
    },

    done: (state, action: PayloadAction<string>) => {
      if (!state.done.includes(action.payload)) {
        state.done.push(action.payload);
      }
    },
  },
});

export const { accept, done } = slice.actions;

export const sentence = (state: RootState): ISentence[] | undefined =>
  state.acceptPr.sentences;

export const doneSent = (state: RootState): string[] | undefined =>
  state.acceptPr.done;

export default slice.reducer;
