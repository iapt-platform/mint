/**
 *
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { ENetStatus } from "../components/general/NetStatus";

import type { RootState } from "../store";

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
