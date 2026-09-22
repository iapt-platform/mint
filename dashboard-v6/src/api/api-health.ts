// src/api/api-health.ts
//
// 心跳检测。**已完全切到 v3**，没有 v2 分支——迁移是一步到位的，
// 要回退就 revert 那个 commit，不在代码里留开关。

import { api, unwrapQuiet } from "./client";
import type { paths } from "./schema";

/** 响应类型直接取自规格，不手写 */
export type Heartbeat = NonNullable<
  paths["/v3/heartbeat"]["get"]["responses"][200]["content"]["application/json"]["data"]
>;

/**
 * 服务是否在正常响应。
 *
 * 用 unwrapQuiet：NetworkStatus 会把失败渲染成状态条，不该再弹窗。
 * 停机维护时后端回 503，这里会抛。
 */
export const apiServerHealth = async (): Promise<Heartbeat> =>
  unwrapQuiet(await api.GET("/v3/heartbeat")).data ?? {};
