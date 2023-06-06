/**
 * 查字典，添加术语命令
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface ITermCommand {}

interface IState {
  open?: "dict" | "channel" | "close";
}

const initialState: IState = {};

export const slice = createSlice({
  name: "right-pannel",
  initialState,
  reducers: {
    //TODO 去掉command
    openPanel: (state, action: PayloadAction<"dict" | "channel" | "close">) => {
      state.open = action.payload;
    },
  },
});

export const { openPanel } = slice.actions;

export const rightPanel = (state: RootState): string | undefined =>
  state.rightPanel.open;

export default slice.reducer;
