import { createSlice, type PayloadAction } from "@reduxjs/toolkit";
import type { Term } from "../api/Term";

import type { RootState } from "../store";

interface IState {
  word?: Term;
  termCache?: Term[];
}

const initialState: IState = {};

export const slice = createSlice({
  name: "term-change",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<Term>) => {
      state.word = action.payload;
      upgrade(action.payload);
    },
    upgrade: (state, action: PayloadAction<Term>) => {
      if (state.termCache) {
        if (
          state.termCache.find(
            (value) => value.word === action.payload.word,
          ) === undefined
        ) {
          state.termCache.push(action.payload);
        }
      } else {
        state.termCache = [action.payload];
      }
    },
  },
});

export const { refresh, upgrade } = slice.actions;

export const changedTerm = (state: RootState): Term | undefined =>
  state.termChange.word;

export const termCache = (state: RootState): Term[] | undefined =>
  state.termChange.termCache;

export default slice.reducer;
