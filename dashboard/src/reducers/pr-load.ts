import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import { ISuggestionData } from "../components/api/Suggestion";

interface IState {
  suggestion?: ISuggestionData;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "pr-load",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<ISuggestionData>) => {
      state.suggestion = action.payload;
    },
  },
});

export const { refresh } = slice.actions;

export const prInfo = (state: RootState): ISuggestionData | undefined =>
  state.prLoad.suggestion;

export default slice.reducer;
