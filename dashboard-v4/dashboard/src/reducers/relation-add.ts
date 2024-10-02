/**
 *
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IRelation } from "../pages/admin/relation/list";

import type { RootState } from "../store";

export interface IRelationParam {
  book: number;
  para: number;
  src_sn?: string;
  target_id?: string;
  target_spell?: string;
  relations?: IRelation[];
  command: "add" | "apply" | "cancel" | "finish";
}
interface IState {
  param?: IRelationParam;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "relation-add",
  initialState,
  reducers: {
    add: (state, action: PayloadAction<IRelationParam>) => {
      state.param = action.payload;
    },
  },
});

export const { add } = slice.actions;

export const relationAddParam = (
  state: RootState
): IRelationParam | undefined => state.relationAdd.param;

export default slice.reducer;
