/**
 *
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import { IDiscussionCountData } from "../components/api/Comment";

export interface IUpgrade {
  resId: string;
  data: IDiscussionCountData[];
}

interface IState {
  list: IDiscussionCountData[];
}

const initialState: IState = { list: [] };

export const slice = createSlice({
  name: "discussion-count",
  initialState,
  reducers: {
    publish: (state, action: PayloadAction<IDiscussionCountData[]>) => {
      console.debug("discussion-count publish", action.payload);
      state.list = action.payload;
    },
    upgrade: (state, action: PayloadAction<IUpgrade>) => {
      console.debug("discussion-count publish", action.payload);
      const old = state.list.filter(
        (value) => value.res_id !== action.payload.resId
      );
      state.list = [...old, ...action.payload.data];
    },
  },
});

export const { publish, upgrade } = slice.actions;

export const discussionList = (
  state: RootState
): IDiscussionCountData[] | undefined => state.discussionCount.list;

export default slice.reducer;
