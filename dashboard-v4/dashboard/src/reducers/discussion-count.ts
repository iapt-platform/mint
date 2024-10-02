/**
 *
 */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import { IDiscussionCountData } from "../components/api/Comment";
import { ITagMapData } from "../components/api/Tag";

export interface IUpgrade {
  resId: string;
  data?: IDiscussionCountData[];
  tags?: ITagMapData[];
}

interface IState {
  list: IDiscussionCountData[];
  tags?: ITagMapData[];
}

const initialState: IState = { list: [] };

export const slice = createSlice({
  name: "discussion-count",
  initialState,
  reducers: {
    discussions: (state, action: PayloadAction<IDiscussionCountData[]>) => {
      console.debug("discussion-count publish", action.payload);
      state.list = action.payload;
    },
    tags: (state, action: PayloadAction<ITagMapData[]>) => {
      console.debug("discussion-count publish", action.payload);
      state.tags = action.payload;
    },
    upgrade: (state, action: PayloadAction<IUpgrade>) => {
      console.debug("discussion-count publish", action.payload);
      const old = state.list.filter(
        (value) => value.res_id !== action.payload.resId
      );

      if (old) {
        if (action.payload.data) {
          state.list = [...old, ...action.payload.data];
        }
      } else {
        if (action.payload.data) {
          state.list = [...action.payload.data];
        }
      }
    },
    tagsUpgrade: (state, action: PayloadAction<IUpgrade>) => {
      console.debug("discussion-count publish", action.payload);
      const old = state.tags?.filter(
        (value) => value.anchor_id !== action.payload.resId
      );
      if (old) {
        if (action.payload.tags) {
          state.tags = [...old, ...action.payload.tags];
        }
      } else {
        if (action.payload.tags) {
          state.tags = [...action.payload.tags];
        }
      }
    },
  },
});

export const { discussions, tags, upgrade, tagsUpgrade } = slice.actions;

export const discussionList = (
  state: RootState
): IDiscussionCountData[] | undefined => state.discussionCount.list;

export const tagList = (state: RootState): ITagMapData[] | undefined =>
  state.discussionCount.tags;

export default slice.reducer;
