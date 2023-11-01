import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface IAuthor {
  name: string;
  email: string;
}

export interface ISite {
  logo: string;
  title: string;
  subhead: string;
  keywords: string[];
  description: string;
  copyright: string;
  author: IAuthor;
}

interface IState {
  site?: ISite;
  title?: string;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "layout",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<ISite>) => {
      state.site = action.payload;
    },
    setTitle: (state, action: PayloadAction<string>) => {
      state.title = action.payload;
    },
  },
});

export const { refresh, setTitle } = slice.actions;

export const layout = (state: RootState): IState => state.layout;

export const siteInfo = (state: RootState): ISite | undefined =>
  state.layout.site;

export const pageTitle = (state: RootState): string | undefined =>
  state.layout.title;

export default slice.reducer;
