import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import { IDictDataRequest } from "../components/api/Dict";

/**
 * 在查询字典后，将查询结果放入map
 * key: 单词词头
 * value: 查询到的单词列表
 */
interface IState {
  wordMap: Map<string, IDictDataRequest[]>;
  word?: string;
  value?: IDictDataRequest[];
}

const initialState: IState = {
  wordMap: new Map<string, IDictDataRequest[]>([["word", []]]),
};

export const slice = createSlice({
  name: "inline-dict",
  initialState,
  reducers: {
    add: (state, action: PayloadAction<IDictDataRequest[]>) => {
      let words: string[] = [];
      for (const iterator of action.payload) {
        if (!words.includes(iterator.word)) {
          words.push(iterator.word);
        }
      }
      /*
      const keys = action.payload.keys();
      for (const key in keys) {
        if (Object.prototype.hasOwnProperty.call(keys, key)) {
          const value = action.payload.get(key);
          if (typeof value !== "undefined") {
            state.wordMap.set(key, value);
          }
        }
      }
*/
    },
  },
});

export const { add } = slice.actions;

export const inlineDict = (state: RootState): IState => state.inlineDict;

export const wordList = (state: RootState): Map<string, IDictDataRequest[]> =>
  state.inlineDict.wordMap;

export default slice.reducer;
