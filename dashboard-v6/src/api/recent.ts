import type { ArticleType } from "./Corpus";

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

export interface IRecent {
  id: string;
  title: string;
  type: ArticleType;
  articleId: string;
  updatedAt: string;
  param?: IRecentParam;
}
