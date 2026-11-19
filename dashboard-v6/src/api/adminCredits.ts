// src/api/adminCredits.ts
//
// 管理后台 · AI 对话积分（v3，仅 root / administrator）：概览、账号一览、流水、手工加减、手工结算。
// 类型全部从 OpenAPI 规格生成（schema.d.ts），这里不手写字段。
import { api, unwrap } from "./client";
import type { paths } from "./schema";

type Json<P extends keyof paths, M extends "get" | "post", S extends 200 | 201 = 200> = paths[P][M] extends {
  responses: { [K in S]: { content: { "application/json": infer B } } };
}
  ? B
  : never;

export type CreditOverview = NonNullable<Json<"/v3/admin/credit-overview", "get">["data"]>;
export type CreditSettlement = NonNullable<CreditOverview["last_settlement"]>;
export type CreditAccountList = Json<"/v3/admin/credit-accounts", "get">;
export type CreditAccount = NonNullable<CreditAccountList["data"]>[number];
export type CreditLedgerList = Json<"/v3/admin/credit-ledger", "get">;
export type CreditLedgerEntry = NonNullable<CreditLedgerList["data"]>[number];
export type CreditAdjustment = Json<"/v3/admin/credit-adjustments", "post", 201>;

export type CreditAccountQuery = NonNullable<paths["/v3/admin/credit-accounts"]["get"]["parameters"]["query"]>;
export type CreditLedgerQuery = NonNullable<paths["/v3/admin/credit-ledger"]["get"]["parameters"]["query"]>;
export type CreditAdjustmentBody = NonNullable<
  paths["/v3/admin/credit-adjustments"]["post"]["requestBody"]
>["content"]["application/json"];
export type CreditSettlementBody = NonNullable<
  paths["/v3/admin/credit-settlements"]["post"]["requestBody"]
>["content"]["application/json"];

export const getCreditOverview = async (): Promise<CreditOverview> =>
  unwrap(await api.GET("/v3/admin/credit-overview")).data as CreditOverview;

export const getCreditAccounts = async (query: CreditAccountQuery): Promise<CreditAccountList> =>
  unwrap(await api.GET("/v3/admin/credit-accounts", { params: { query } }));

export const getCreditLedger = async (query: CreditLedgerQuery): Promise<CreditLedgerList> =>
  unwrap(await api.GET("/v3/admin/credit-ledger", { params: { query } }));

/** 手工加减积分。表单要字段级错误（用户不存在、余额不够扣），调用方 catch 后用 fieldErrorsOf。 */
export const adjustCredits = async (body: CreditAdjustmentBody): Promise<CreditAdjustment> =>
  unwrap(await api.POST("/v3/admin/credit-adjustments", { body })) as CreditAdjustment;

export const settleCredits = async (body: CreditSettlementBody): Promise<CreditSettlement> =>
  unwrap(await api.POST("/v3/admin/credit-settlements", { body })).data as CreditSettlement;
