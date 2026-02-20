/**
 *
 */
import { createSlice, type PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export type TPanelName =
  | "dict"
  | "channel"
  | "discussion"
  | "suggestion"
  | "grammar"
  | "close"
  | "open";
interface IState {
  open?: TPanelName;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "right-pannel",
  initialState,
  reducers: {
    //TODO 去掉command
    openPanel: (state, action: PayloadAction<TPanelName | undefined>) => {
      state.open = action.payload;
    },
  },
});

export const { openPanel } = slice.actions;

export const rightPanel = (state: RootState): TPanelName | undefined =>
  state.rightPanel.open;

export default slice.reducer;
