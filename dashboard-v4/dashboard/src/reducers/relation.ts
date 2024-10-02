/**
 * 从服务器获取的术语表
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IRelation } from "../pages/admin/relation/list";

import type { RootState } from "../store";

interface IState {
  relations?: IRelation[];
}

const initialState: IState = {};

export const slice = createSlice({
  name: "relation",
  initialState,
  reducers: {
    pushRelation: (state, action: PayloadAction<IRelation[]>) => {
      state.relations = action.payload;
    },
  },
});

export const { pushRelation } = slice.actions;

export const getRelation = (state: RootState): IRelation[] | undefined =>
  state.relation.relations;

export default slice.reducer;
