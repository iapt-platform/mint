// src/api/adminStats.ts
//
// 管理后台统计（v3，仅 root / administrator）：阅读统计、AI 对话统计、提问与研究过程。
// 类型全部从 OpenAPI 规格生成（schema.d.ts），这里不手写字段。
import { api, unwrap } from "./client";
import type { paths } from "./schema";

type Json<P extends keyof paths, M extends "get"> = paths[P][M] extends {
  responses: { 200: { content: { "application/json": infer B } } };
}
  ? B
  : never;

export type ReadingStats = NonNullable<Json<"/v3/admin/reading-stats", "get">["data"]>;
export type AgentStats = NonNullable<Json<"/v3/admin/agent-stats", "get">["data"]>;
export type AgentRunList = Json<"/v3/admin/agent-runs", "get">;
export type AgentRun = NonNullable<AgentRunList["data"]>[number];
/** 研究路径枚举，来自 OpenAPI（mint AgentRun::ROUTES）。 */
export type AgentRoute = NonNullable<AgentRun["route"]>;
export type AgentRunDetail = NonNullable<Json<"/v3/admin/agent-runs/{run}", "get">["data"]>;

export type AgentFeedbackList = Json<"/v3/admin/agent-feedback", "get">;
export type AgentFeedbackItem = NonNullable<AgentFeedbackList["data"]>[number];
export type AgentFeedbackQuery = NonNullable<paths["/v3/admin/agent-feedback"]["get"]["parameters"]["query"]>;

export type ReadingStatsQuery = NonNullable<paths["/v3/admin/reading-stats"]["get"]["parameters"]["query"]>;
export type AgentStatsQuery = NonNullable<paths["/v3/admin/agent-stats"]["get"]["parameters"]["query"]>;
export type AgentRunQuery = NonNullable<paths["/v3/admin/agent-runs"]["get"]["parameters"]["query"]>;

export const getReadingStats = async (query: ReadingStatsQuery): Promise<ReadingStats> =>
  unwrap(await api.GET("/v3/admin/reading-stats", { params: { query } })).data as ReadingStats;

export const getAgentStats = async (query: AgentStatsQuery): Promise<AgentStats> =>
  unwrap(await api.GET("/v3/admin/agent-stats", { params: { query } })).data as AgentStats;

export const getAgentRuns = async (query: AgentRunQuery): Promise<AgentRunList> =>
  unwrap(await api.GET("/v3/admin/agent-runs", { params: { query } }));

export const getAgentRun = async (run: string): Promise<AgentRunDetail> =>
  unwrap(await api.GET("/v3/admin/agent-runs/{run}", { params: { path: { run } } })).data as AgentRunDetail;

export const getAgentFeedback = async (query: AgentFeedbackQuery): Promise<AgentFeedbackList> =>
  unwrap(await api.GET("/v3/admin/agent-feedback", { params: { query } }));
