/**
 *
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

interface IState {
  mode?: string;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "cart-mode",
  initialState,
  reducers: {
    modeChange: (state, action: PayloadAction<string>) => {
      state.mode = action.payload;
    },
  },
});

export const { modeChange } = slice.actions;

export const mode = (state: RootState): string | undefined =>
  state.cartMode.mode;

export default slice.reducer;
