import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

interface IState {
  title: string;
  subhead: string;
  copyright: string;
  languages: string[];
}

const initialState: IState = {
  title: "",
  subhead: "",
  copyright: "",
  languages: [],
};

const slice = createSlice({
  name: "site-info",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<IState>) => {
      Object.assign(state, action.payload);
    },
  },
});

export const { refresh } = slice.actions;

export const selectSiteInfo = (state: RootState): IState => {
  return { ...state.siteInfo };
};

export default slice.reducer;
