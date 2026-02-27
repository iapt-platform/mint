import { get } from "../request";
import type { ArticleType } from "./Article";

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

export const getRecentByUser = async (
  userId: string,
  pageSize: number,
  page: number = 0
): Promise<IRecentListResponse> => {
  let url = `/api/v2/recent?view=user&id=${userId}`;
  url += `&limit=${pageSize}&offset=${page}`;
  console.log("url", url);
  return await get<IRecentListResponse>(url);
};
