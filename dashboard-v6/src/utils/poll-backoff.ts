// src/utils/poll-backoff.ts
//
// 后台轮询（心跳、通知角标）共用的间隔计算。
//
// 后端过载或宕机时，轮询失败越快、重发越密，就越压着后端起不来
// （2026-10-28 线上 502 期间出现过每秒上百次的心跳 + 通知请求）。
// 所以失败后按指数拉长间隔，成功一次就回到基础间隔。

/** 基础轮询间隔：5 分钟 */
export const POLL_BASE_MS = 5 * 60_000;

/** 失败退避的上限：30 分钟 */
export const POLL_MAX_MS = 30 * 60_000;

/**
 * 连续失败 `failures` 次后，距下一次轮询的等待时间。
 *
 * 0 次 → 5 分钟，1 次 → 10 分钟，2 次 → 20 分钟，之后封顶 30 分钟。
 * 加 ±10% 抖动，避免多个标签页、多个用户在后端恢复的同一刻一起打上来。
 */
export const nextPollDelay = (failures: number): number => {
  const delay = Math.min(POLL_BASE_MS * 2 ** failures, POLL_MAX_MS);
  return delay * (0.9 + Math.random() * 0.2);
};
