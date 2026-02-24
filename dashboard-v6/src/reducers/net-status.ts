/**
 *
 */
import { createSlice, type PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
export type ENetStatus = "loading" | "success" | "fail";
export interface INetStatus {
  message?: string;
  status?: ENetStatus;
}
interface IState {
  status?: INetStatus;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "netStatus",
  initialState,
  reducers: {
    statusChange: (state, action: PayloadAction<INetStatus>) => {
      state.status = action.payload;
    },
  },
});

export const { statusChange } = slice.actions;

export const netStatus = (state: RootState): INetStatus | undefined =>
  state.netStatus.status;

export default slice.reducer;
