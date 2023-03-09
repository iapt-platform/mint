/**
 * 查字典，添加术语命令
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IWidgetDict } from "../components/dict/DictComponent";
import { ISentence } from "../components/template/SentEdit";
import { IWidgetDictCreate } from "../components/term/TermCreate";

import type { RootState } from "../store";

interface IState {
  sentence?: ISentence;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "accept-pr",
  initialState,
  reducers: {
    accept: (state, action: PayloadAction<ISentence>) => {
      state.sentence = action.payload;
    },
  },
});

export const { accept } = slice.actions;

export const sentence = (state: RootState): ISentence | undefined =>
  state.acceptPr.sentence;

export default slice.reducer;
