/**
 *
 */
import { createSlice, type PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export type TCopyMode = "batch" | "single";

interface IState {
  mode: TCopyMode;
}

const initialState: IState = {
  mode: "single",
};

export const slice = createSlice({
  name: "cart-mode",
  initialState,
  reducers: {
    modeChange: (state, action: PayloadAction<TCopyMode>) => {
      state.mode = action.payload;
    },
  },
});

export const { modeChange } = slice.actions;

export const mode = (state: RootState): TCopyMode => state.cartMode.mode;

export default slice.reducer;
