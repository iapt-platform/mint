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
  /** 讨论面板「本章」列表选中的那一句（句子 id），正文持续高亮它 */
  highlight?: string;
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
    setHighlight: (state, action: PayloadAction<string | undefined>) => {
      state.highlight = action.payload;
    },
  },
});

export const { show, showAnchor, setHighlight } = slice.actions;

export const message = (state: RootState): IShowDiscussion | undefined =>
  state.discussion.message;
export const anchor = (state: RootState): IShowDiscussion | undefined =>
  state.discussion.anchor;
export const highlight = (state: RootState): string | undefined =>
  state.discussion.highlight;
export default slice.reducer;
