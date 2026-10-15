/**
 * 讨论角标：句子 / 逐词上未关闭的评论线程数，以及其中我发起的。
 *
 * 角标组件（DiscussionButton）挂载时登记自己的对象，DiscussionBadgeLoader 把登记的
 * 对象攒一批，调 `POST /v3/discussions/batch-count` 填回来。
 */
import { createSlice, type PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import type { TDiscussionBadge, TDiscussionBadgeTarget } from "../api/discussion";

export interface IBadgeCount {
  total: number;
  mine: number;
}

interface IState {
  /** 登记过的对象，键为 `type/id` */
  targets: string[];
  counts: Record<string, IBadgeCount>;
}

const initialState: IState = { targets: [], counts: {} };

export const badgeKey = (target: TDiscussionBadgeTarget): string =>
  `${target.type}/${target.id}`;

export const slice = createSlice({
  name: "discussion-badge",
  initialState,
  reducers: {
    register: (state, action: PayloadAction<TDiscussionBadgeTarget>) => {
      const key = badgeKey(action.payload);
      if (!state.targets.includes(key)) {
        state.targets.push(key);
      }
    },
    setCounts: (state, action: PayloadAction<TDiscussionBadge[]>) => {
      for (const item of action.payload) {
        state.counts[`${item.target_type}/${item.target_id}`] = {
          total: item.total ?? 0,
          mine: item.mine ?? 0,
        };
      }
    },
  },
});

export const { register, setCounts } = slice.actions;

export const badgeTargets = (state: RootState): string[] =>
  state.discussionBadge.targets;

export const badgeCounts = (state: RootState): Record<string, IBadgeCount> =>
  state.discussionBadge.counts;

export default slice.reducer;
