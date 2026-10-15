/**
 *
 */
import { createSlice, type PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import type { ISentence } from "../api/sentence";
import type { TResType } from "../api/discussion";

export interface IShowDiscussion {
  type: "discussion" | "pr";
  resType?: TResType;
  resId?: string;
  topic?: string;
  comment?: string;
  sent?: ISentence;
  withStudent?: boolean;
}
interface IState {
  message?: IShowDiscussion;
  anchor?: IShowDiscussion;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "discussion",
  initialState,
  reducers: {
    show: (state, action: PayloadAction<IShowDiscussion>) => {
      state.message = action.payload;
    },
    showAnchor: (state, action: PayloadAction<IShowDiscussion>) => {
      state.anchor = action.payload;
    },
  },
});

export const { show, showAnchor } = slice.actions;

export const message = (state: RootState): IShowDiscussion | undefined =>
  state.discussion.message;
export const anchor = (state: RootState): IShowDiscussion | undefined =>
  state.discussion.anchor;
export default slice.reducer;
