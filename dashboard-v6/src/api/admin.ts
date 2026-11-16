// src/api/admin.ts
//
// 管理后台（/admin）用到的 v2 接口。自 dashboard-v4 的 pages/admin 迁来，
// 接口与 v4 完全一致，v2 一律走 request.ts（见 mint 根目录 CLAUDE.md「前端 dashboard-v6」）。

import { delete_, get, post, put } from "../request";
import type { IDeleteResponse } from "./group";
import type { IFrom, IRelationRequest, IRelationListResponse, IRelationResponse } from "./relation";
import type { IUserListResponse2 } from "./Auth";
import type { IAiModelListResponse, IAiModelResponse, IAiModelSystem } from "./ai";
import type { IUser } from "./Auth";

/* ---------------------------------------------------------------- relation */

export const fetchRelationList = (query: string) =>
  get<IRelationListResponse>(`/api/v2/relation?view=a${query}`);

export const fetchRelation = (id: string) => get<IRelationResponse>(`/api/v2/relation/${id}`);

export const saveRelation = (data: IRelationRequest, id?: string) =>
  id
    ? put<IRelationRequest, IRelationResponse>(`/api/v2/relation/${id}`, data)
    : post<IRelationRequest, IRelationResponse>(`/api/v2/relation`, data);

export const deleteRelation = (id: string) => delete_<IDeleteResponse>(`/api/v2/relation/${id}`);

export const RELATION_IMPORT_URL = "/api/v2/relation-import";
export const RELATION_EXPORT_URL = "/api/v2/relation-export";

/* ---------------------------------------------------------- nissaya ending */

export interface INissayaEndingRequest {
  id?: string;
  ending?: string;
  lang?: string;
  relation?: string;
  case?: string;
  from?: IFrom | null;
  editor?: IUser;
  count?: number;
  term_id?: string;
  term_channel?: string;
  updated_at?: string;
  created_at?: string;
}
export interface INissayaEndingListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: INissayaEndingRequest[];
    count: number;
  };
}
export interface INissayaEndingResponse {
  ok: boolean;
  message: string;
  data: INissayaEndingRequest;
}
/** 表格 / 表单里的一行（from 拆成 fromCase、fromSpell 两个表单项）。 */
export interface INissayaEnding {
  sn?: number;
  id?: string;
  ending?: string;
  lang?: string;
  relation?: string;
  case?: string;
  from?: IFrom | null;
  fromCase?: string[];
  fromSpell?: string;
  count?: number;
  termId?: string;
  termChannel?: string;
  updated_at?: string;
}

export const fetchNissayaEndingList = (query: string) =>
  get<INissayaEndingListResponse>(`/api/v2/nissaya-ending?view=a${query}`);

export const fetchNissayaEnding = (id: string) =>
  get<INissayaEndingResponse>(`/api/v2/nissaya-ending/${id}`);

export const saveNissayaEnding = (data: INissayaEndingRequest, id?: string) =>
  id
    ? put<INissayaEndingRequest, INissayaEndingResponse>(`/api/v2/nissaya-ending/${id}`, data)
    : post<INissayaEndingRequest, INissayaEndingResponse>(`/api/v2/nissaya-ending`, data);

export const deleteNissayaEnding = (id: string) =>
  delete_<IDeleteResponse>(`/api/v2/nissaya-ending/${id}`);

export const NISSAYA_ENDING_IMPORT_URL = "/api/v2/nissaya-ending-import";
export const NISSAYA_ENDING_EXPORT_URL = "/api/v2/nissaya-ending-export";

/* -------------------------------------------------------------------- users */

/** 全站用户列表（管理员视图）。query 以 & 开头。 */
export const fetchUserList = (query: string) =>
  get<IUserListResponse2>(`/api/v2/user?view=all&order=created_at&dir=desc${query}`);

/* --------------------------------------------------------------- dictionary */

export interface IDictStatistic {
  key: string;
  title: string;
  count: number;
  vocabulary: number;
  parent: number;
}
interface IDictStatisticResponse {
  ok: boolean;
  message: string;
  data: IDictStatistic[];
}

export const fetchDictStatistic = () => get<IDictStatisticResponse>("/api/v2/dict-statistic");

/* -------------------------------------------------------------- AI 系统模型 */

/** 可选模型（chat 视图下全站可用的模型）。 */
export const fetchChatModels = () => get<IAiModelListResponse>("/api/v2/ai-model?view=chat");

/** 保存某一类用途（wbw / chat / summarize）的系统模型列表。 */
export const saveSystemModels = (view: string, models: string[]) =>
  post<IAiModelSystem, IAiModelResponse>("/api/v2/system-model", { view, models });

/* -------------------------------------------------------------- API 监控 */

export interface IApiStatPoint {
  date: string;
  value: number;
}
interface IApiStatResponse {
  ok: boolean;
  message: string;
  data: IApiStatPoint[];
}
interface IApiAverageResponse {
  ok: boolean;
  message: string;
  data: number;
}
export type TApiStatItem = "average" | "count" | "delay";

/** 近 10 分钟 / 小时的接口统计序列（v4 原样：PUT /v2/api/10）。 */
export const fetchApiStat = (item: TApiStatItem, api = "all") =>
  put<{ api: string; item: string }, IApiStatResponse>("/api/v2/api/10", { api, item });

/** 当前平均响应时间（毫秒）。 */
export const fetchApiAverage = () => get<IApiAverageResponse>("/api/v2/api/10?item=average");
