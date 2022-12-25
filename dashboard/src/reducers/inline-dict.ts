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
      let words: string[] = [];
      let newWordData = new Array(...state.wordList);
      let newIndexData = new Array(...state.wordIndex);
      //查询没有的词并添加
      for (const iterator of action.payload) {
        if (!newIndexData.includes(iterator.word)) {
          if (!words.includes(iterator.word)) {
            words.push(iterator.word);
          }
          newWordData.push(iterator);
        }
      }
      newIndexData = [...newIndexData, ...words];
      state.wordList = newWordData;
      state.wordIndex = newIndexData;
      console.log("add inline dict", words);
    },
  },
});

export const { add } = slice.actions;

export const inlineDict = (state: RootState): IState => state.inlineDict;

export const wordList = (state: RootState): IApiResponseDictData[] =>
  state.inlineDict.wordList;
export const wordIndex = (state: RootState): string[] =>
  state.inlineDict.wordIndex;
export default slice.reducer;
