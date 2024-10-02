import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { ITermDataResponse } from "../components/api/Term";

import type { RootState } from "../store";

interface IState {
  word?: ITermDataResponse;
  termCache?: ITermDataResponse[];
}

const initialState: IState = {};

export const slice = createSlice({
  name: "term-change",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<ITermDataResponse>) => {
      state.word = action.payload;
      upgrade(action.payload);
    },
    upgrade: (state, action: PayloadAction<ITermDataResponse>) => {
      if (state.termCache) {
        if (
          state.termCache.find(
            (value) => value.word === action.payload.word
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

export const changedTerm = (state: RootState): ITermDataResponse | undefined =>
  state.termChange.word;

export const termCache = (state: RootState): ITermDataResponse[] | undefined =>
  state.termChange.termCache;

export default slice.reducer;
