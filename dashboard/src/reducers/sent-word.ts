import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

interface ISentWord {
  sentId: string;
  words: string[];
}
/**
 * 在查询字典后，将查询结果放入map
 * key: 单词词头
 * value: 查询到的单词列表
 */
interface IState {
  wordList: ISentWord[];
  changedSent: string;
}

const initialState: IState = {
  wordList: [],
  changedSent: "",
};

export const slice = createSlice({
  name: "sent-words",
  initialState,
  reducers: {
    add: (state, action: PayloadAction<ISentWord>) => {
      const sentIndex = state.wordList.findIndex(
        (value) => value.sentId === action.payload.sentId
      );
      if (sentIndex >= 0) {
        state.wordList[sentIndex] = action.payload;
        state.changedSent = action.payload.sentId;
      } else {
        state.wordList.push(action.payload);
        state.changedSent = action.payload.sentId;
      }
    },
  },
});

export const { add } = slice.actions;

export const wordList = (state: RootState): ISentWord[] =>
  state.sentWords.wordList;
export const changedSent = (state: RootState): string =>
  state.sentWords.changedSent;

export default slice.reducer;
