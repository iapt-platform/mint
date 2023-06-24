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
  },
});

export const { command } = slice.actions;
export const { lookup } = slice.actions;
export const commandParam = (state: RootState): IState => state.command;

export const message = (state: RootState): ICommand | undefined =>
  state.command.message;
export const lookupWord = (state: RootState): string | undefined =>
  state.command.lookup;

export default slice.reducer;
