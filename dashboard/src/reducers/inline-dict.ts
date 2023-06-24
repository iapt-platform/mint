import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import { IApiResponseDictData } from "../components/api/Dict";

/**
 * 在查询字典后，将查询结果放入map
 * key: 单词词头
 * value: 查询到的单词列表
 */
interface IState {
  wordList: IApiResponseDictData[];
  wordIndex: string[];
}

const initialState: IState = {
  wordList: [],
  wordIndex: [],
};

export const slice = createSlice({
  name: "inline-dict",
  initialState,
  reducers: {
    add: (state, action: PayloadAction<IApiResponseDictData[]>) => {
      let wordIndex: string[] = [];
      let newWordData = new Array(...state.wordList);
      let newIndexData = new Array(...state.wordIndex);
      //查询没有的词并添加
      for (const iterator of action.payload) {
        if (!newIndexData.includes(iterator.word)) {
          if (!wordIndex.includes(iterator.word)) {
            wordIndex.push(iterator.word);
          }
          newWordData.push(iterator);
        }
      }
      newIndexData = [...newIndexData, ...wordIndex];
      state.wordList = newWordData;
      state.wordIndex = newIndexData;
    },
    updateIndex: (state, action: PayloadAction<string[]>) => {
      action.payload.forEach((value) => {
        if (!state.wordIndex.includes(value)) {
          state.wordIndex.push(value);
        }
      });
    },
  },
});

export const { add, updateIndex } = slice.actions;

export const inlineDict = (state: RootState): IState => state.inlineDict;

export const wordList = (state: RootState): IApiResponseDictData[] =>
  state.inlineDict.wordList;
export const wordIndex = (state: RootState): string[] =>
  state.inlineDict.wordIndex;
export default slice.reducer;
