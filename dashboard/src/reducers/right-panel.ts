/**
 *
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { TPanelName } from "../components/article/RightPanel";

import type { RootState } from "../store";

export interface ITermCommand {}

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
