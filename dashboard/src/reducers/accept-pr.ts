/**
 *
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { ISentence } from "../components/template/SentEdit";

import type { RootState } from "../store";

interface IState {
  sentences?: ISentence[];
  dirty: string[];
}

const initialState: IState = { dirty: [] };

export const slice = createSlice({
  name: "accept-pr",
  initialState,
  reducers: {
    accept: (state, action: PayloadAction<ISentence[]>) => {
      state.sentences = action.payload;
      state.dirty = [];
    },

    remove: (state, action: PayloadAction<string>) => {
      if (!state.dirty.includes(action.payload)) {
        state.dirty.push(action.payload);
      }
    },
  },
});

export const { accept, remove } = slice.actions;

export const sentence = (state: RootState): ISentence[] | undefined =>
  state.acceptPr.sentences;

export const dirtySent = (state: RootState): string[] | undefined =>
  state.acceptPr.dirty;

export default slice.reducer;
