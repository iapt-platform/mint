import { ArticleType } from "../article/Article";

export interface IViewRequest {
  target_type: ArticleType;
  book: number;
  para: number;
  channel: string;
  mode: string;
}
export interface IMetaChapter {
  book: number;
  para: number;
  channel: string;
  mode: string;
}
export interface IViewData {
  id: string;
  target_id: string;
  target_type: ArticleType;
  updated_at: string;
  title: string;
  org_title: string;
  meta: string;
}
export interface IViewStoreResponse {
  ok: boolean;
  message: string;
  data: number;
}
export interface IViewResponse {
  ok: boolean;
  message: string;
  data: IViewData;
}
export interface IViewListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IViewData[];
    count: number;
  };
}

export interface IView {
  id: string;
  title: string;
  subtitle: string;
  type: ArticleType;
  updatedAt: string;
  meta: IMetaChapter;
}
