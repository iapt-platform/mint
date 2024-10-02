/**
 *
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface ITermCommand {}

export interface IShowWbw {
  id?: string;
  channel?: string;
}

interface IState {
  anchor?: IShowWbw;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "wbw",
  initialState,
  reducers: {
    showWbw: (state, action: PayloadAction<IShowWbw>) => {
      state.anchor = action.payload;
    },
  },
});

export const { showWbw } = slice.actions;

export const anchor = (state: RootState): IShowWbw | undefined =>
  state.wbw.anchor;
export default slice.reducer;
