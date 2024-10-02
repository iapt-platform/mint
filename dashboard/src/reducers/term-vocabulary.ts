/**
 * 从服务器获取的术语表
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface ITerm {
  guid?: string;
  word: string;
  tag?: string;
  meaning: string;
  other_meaning?: string;
}

interface IState {
  term?: ITerm[];
  grammar?: ITerm[];
}

const initialState: IState = {};

export const slice = createSlice({
  name: "term-vocabulary",
  initialState,
  reducers: {
    update: (state, action: PayloadAction<ITerm[]>) => {
      state.term = action.payload;
    },
    grammar: (state, action: PayloadAction<ITerm[]>) => {
      state.grammar = action.payload;
    },
    push: (state, action: PayloadAction<ITerm>) => {
      if (state.term) {
        if (
          state.term.find((value) => value.word === action.payload.word) ===
          undefined
        ) {
          state.term.push(action.payload);
        }
      } else {
        state.term = [action.payload];
      }
    },
  },
});

export const { update, grammar, push } = slice.actions;

export const getTerm = (state: RootState): ITerm[] | undefined =>
  state.termVocabulary.term;
export const getGrammar = (state: RootState): ITerm[] | undefined =>
  state.termVocabulary.grammar;
export default slice.reducer;
