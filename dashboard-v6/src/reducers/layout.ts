import { createSlice, type PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface IRefresh {
  title: string;
  subhead: string;
  description: string;
  copyright: string;
  version: string;
}

interface LayoutState {
  title?: string;
  subhead?: string;
  description?: string;
  copyright?: string;
  version?: string;
}

const initialState: LayoutState = {};

export const layoutSlice = createSlice({
  name: "layout",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<IRefresh>) => {
      state.version = action.payload.version;
      state.subhead = action.payload.subhead;
      state.description = action.payload.description;
      state.copyright = action.payload.copyright;
      state.version = action.payload.version;
    },
  },
});

export const { refresh } = layoutSlice.actions;

export const selectVersion = (state: RootState) => state.layout.version;

export default layoutSlice.reducer;
