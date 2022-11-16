import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface IOpenArticle {
  type: string;
  articleId: string;
}

interface IState {
  article?: IOpenArticle;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "open-article",
  initialState,
  reducers: {
    openArticle: (state, action: PayloadAction<IOpenArticle>) => {
      state.article = action.payload;
    },
  },
});

export const open = slice.actions;

export const openArticle = (state: RootState): IState => state.openArticle;

export const articleInfo = (state: RootState): IOpenArticle | undefined =>
  state.openArticle.article;

export default slice.reducer;
