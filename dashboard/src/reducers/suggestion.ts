/**
 * 对资源的评论等信息
 * 包括
 * 1.修改建议
 * 2.问答
 * 3.评论
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface ISuggestionItem {
  id: string;
  type: "sent" | "lesson" | "article";
  count: number;
  text: string[];
}

interface IState {
  suggestions?: ISuggestionItem[];
  current?: ISuggestionItem;
}

const initialState: IState = {};

const KEY = "suggestion";

const set = (settings: ISuggestionItem[]) => {
  sessionStorage.setItem(KEY, JSON.stringify(settings));
};

export const slice = createSlice({
  name: "suggestion",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<ISuggestionItem[]>) => {
      state.suggestions = action.payload;
    },
    onChange: (state, action: PayloadAction<ISuggestionItem>) => {
      state.current = action.payload;
      //将新的改变放入 store
      if (typeof state.suggestions !== "undefined") {
        const index = state.suggestions.findIndex(
          (element) => element.id === action.payload.id
        );
        if (index >= 0) {
          //找到旧的记录-更新
          state.suggestions[index] = action.payload;
        } else {
          //没找到-新建
          state.suggestions.push(action.payload);
        }
      } else {
        state.suggestions = [action.payload];
      }
      set(state.suggestions);
    },
  },
});

export const { refresh, onChange } = slice.actions;

export const suggestion = (state: RootState): IState => state.suggestion;

export const allSuggestion = (
  state: RootState
): ISuggestionItem[] | undefined => state.suggestion.suggestions;

export const current = (state: RootState): ISuggestionItem | undefined =>
  state.suggestion.current;

export default slice.reducer;
