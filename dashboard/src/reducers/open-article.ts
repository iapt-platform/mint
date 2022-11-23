import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface ISite {
  title: string;
  url: string;
  id: string;
}

interface IState {
  site?: ISite;
  title?: string;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "open-article",
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

export const openArticle = (state: RootState): IState => state.openArticle;

export const siteInfo = (state: RootState): ISite | undefined =>
  state.openArticle.site;

export const pageTitle = (state: RootState): string | undefined =>
  state.openArticle.title;

export default slice.reducer;
