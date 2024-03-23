/**
 * 查字典，添加术语命令
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IWidgetDict } from "../components/dict/DictComponent";
import { ITerm } from "../components/term/TermEdit";

import type { RootState } from "../store";

export interface ITermCommand {}

export interface ICommand {
  prop?: ITerm | IWidgetDict;
  type?: "term" | "dict";
}
interface IState {
  message?: ICommand;
  command?: "term" | "dict";
  lookup?: string;
  grammar?: string;
  grammarId?: string;
  myDictDirty?: boolean;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "command",
  initialState,
  reducers: {
    //TODO 去掉command
    command: (state, action: PayloadAction<ICommand>) => {
      state.message = action.payload;
    },
    lookup: (state, action: PayloadAction<string | undefined>) => {
      state.lookup = action.payload;
    },
    myDictDirty: (state, action: PayloadAction<boolean | undefined>) => {
      state.myDictDirty = action.payload;
    },
    grammar: (state, action: PayloadAction<string | undefined>) => {
      state.grammar = action.payload;
    },
    grammarId: (state, action: PayloadAction<string | undefined>) => {
      state.grammarId = action.payload;
    },
  },
});

export const { command } = slice.actions;
export const { lookup } = slice.actions;
export const { myDictDirty } = slice.actions;
export const { grammar } = slice.actions;
export const { grammarId } = slice.actions;

export const commandParam = (state: RootState): IState => state.command;

export const message = (state: RootState): ICommand | undefined =>
  state.command.message;
export const lookupWord = (state: RootState): string | undefined =>
  state.command.lookup;
export const grammarWord = (state: RootState): string | undefined =>
  state.command.grammar;
export const grammarWordId = (state: RootState): string | undefined =>
  state.command.grammarId;
export const myDictIsDirty = (state: RootState): boolean | undefined =>
  state.command.myDictDirty;

export default slice.reducer;
