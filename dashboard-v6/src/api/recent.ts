// src/api/recent.ts
import { get, post } from "../request";
import type { ArticleType } from "./article";

export interface IRecent {
  id: string;
  title: string;
  type: ArticleType;
  articleId: string;
  updatedAt: string;
  param?: IRecentParam;
}
export interface IRecentRequest {
  type: ArticleType;
  article_id: string;
  param?: string;
}
export interface IRecentParam {
  book?: string;
  para?: string;
  channel?: string;
  mode?: string;
}
export interface IRecentData {
  id: string;
  title: string;
  type: ArticleType;
  article_id: string;
  param: string | null;
  updated_at: string;
}

export interface IRecentResponse {
  ok: boolean;
  message: string;
  data: IRecentData;
}

export interface IRecentListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IRecentData[];
    count: number;
  };
}

// 新增
export interface IGetRecentByUserParams {
  userId: string;
  pageSize: number;
  page?: number;
  type?: ArticleType;
}

// 修改
export const getRecentByUser = async (
  params: IGetRecentByUserParams
): Promise<IRecentListResponse> => {
  const { userId, pageSize, page = 0, type } = params;

  let url = `/api/v2/recent?view=user&id=${userId}`;
  url += `&limit=${pageSize}&offset=${page}`;
  if (type !== undefined) url += `&type=${type}`;

  console.log("url", url);
  return await get<IRecentListResponse>(url);
};

export interface ISaveRecentRequest {
  type: ArticleType;
  article_id: string;
  param?: string;
}

export const saveRecent = async (
  payload: ISaveRecentRequest
): Promise<IRecentResponse> => {
  return await post<ISaveRecentRequest, IRecentResponse>(
    "/api/v2/recent",
    payload
  );
};
