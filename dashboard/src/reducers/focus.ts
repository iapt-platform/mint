import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface IFocus {
  type: string;
  id?: string | null;
}

interface IState {
  focus?: IFocus;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "focus",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<IFocus>) => {
      state.focus = action.payload;
    },
  },
});

export const { refresh } = slice.actions;

export const currFocus = (state: RootState): IState => state.focus;

export default slice.reducer;
