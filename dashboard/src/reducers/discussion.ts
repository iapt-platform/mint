/**
 *
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import { TResType } from "../components/discussion/DiscussionListCard";
import { ISentence } from "../components/template/SentEdit";

import type { RootState } from "../store";

export interface ITermCommand {}

export interface IShowDiscussion {
  type: "discussion" | "pr";
  resType?: TResType;
  resId?: string;
  topic?: string;
  comment?: string;
  sent?: ISentence;
  withStudent?: boolean;
}
export interface ICount {
  count: number;
  resType?: TResType;
  resId?: string;
}
interface IState {
  message?: IShowDiscussion;
  count?: ICount;
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
    countChange: (state, action: PayloadAction<ICount>) => {
      state.count = action.payload;
    },
    showAnchor: (state, action: PayloadAction<IShowDiscussion>) => {
      state.anchor = action.payload;
    },
  },
});

export const { show, countChange, showAnchor } = slice.actions;

export const message = (state: RootState): IShowDiscussion | undefined =>
  state.discussion.message;
export const count = (state: RootState): ICount | undefined =>
  state.discussion.count;
export const anchor = (state: RootState): IShowDiscussion | undefined =>
  state.discussion.anchor;
export default slice.reducer;
