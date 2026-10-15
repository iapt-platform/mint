/**
 * 句子 / 逐词上的标签（tag_maps），由 TagsLoader 批量取、TagsOnItem 改完后局部更新。
 *
 * 原先和讨论角标一起放在 discussion-count reducer 里（v2 的同一个接口一起返回），
 * 讨论角标迁 v3 时拆开了。
 */
import { createSlice, type PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import type { ITagMapData } from "../api/tag";

export interface ITagsUpgrade {
  resId: string;
  tags?: ITagMapData[];
}

interface IState {
  tags?: ITagMapData[];
}

const initialState: IState = {};

export const slice = createSlice({
  name: "tag-map",
  initialState,
  reducers: {
    tags: (state, action: PayloadAction<ITagMapData[]>) => {
      state.tags = action.payload;
    },
    tagsUpgrade: (state, action: PayloadAction<ITagsUpgrade>) => {
      if (!action.payload.tags) {
        return;
      }
      const others = (state.tags ?? []).filter(
        (value) => value.anchor_id !== action.payload.resId
      );
      state.tags = [...others, ...action.payload.tags];
    },
  },
});

export const { tags, tagsUpgrade } = slice.actions;

export const tagList = (state: RootState): ITagMapData[] | undefined =>
  state.tagMap.tags;

export default slice.reducer;
