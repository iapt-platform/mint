/**
 * 查字典，添加术语命令
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IWidgetDict } from "../components/dict/DictComponent";
import { IWidgetDictCreate } from "../components/term/TermCreate";

import type { RootState } from "../store";

export interface ICommand {
  prop?: IWidgetDictCreate | IWidgetDict;
  type?: "term" | "dict";
}
interface IState {
  message?: ICommand;
  command?: "term" | "dict";
}

const initialState: IState = {};

export const slice = createSlice({
  name: "command",
  initialState,
  reducers: {
    command: (state, action: PayloadAction<ICommand>) => {
      state.message = action.payload;
      console.log("command", action.payload);
    },
  },
});

export const { command } = slice.actions;
export const commandParam = (state: RootState): IState => state.command;

export const message = (state: RootState): ICommand | undefined =>
  state.command.message;

export default slice.reducer;
