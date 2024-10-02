/**
 * 
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface ITermItem {
  word: string;
  channelId: string;
  first: string;
}
interface IState {
  order: ITermItem[];
}

const initialState: IState = { order: [] };

export const slice = createSlice({
  name: "term-order",
  initialState,
  reducers: {
    push: (state, action: PayloadAction<ITermItem>) => {
      const index = state.order.findIndex(
        (value) =>
          value.word === action.payload.word &&
          value.channelId === action.payload.channelId
      );
      if (index === -1) {
        state.order.push(action.payload);
      }
    },
  },
});

export const { push } = slice.actions;

export const order = (state: RootState): ITermItem[] | undefined =>
  state.termOrder.order;

export default slice.reducer;
