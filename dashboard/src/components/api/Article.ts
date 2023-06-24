import { IStudio } from "../auth/StudioName";
import { IUser } from "../auth/User";
import { ITocPathNode } from "../corpus/TocPath";
import type { IStudioApiResponse, TRole } from "./Auth";

export interface IArticleListApiResponse {
  article: string;
  title: string;
  level: string;
  children: number;
}
export interface IAnthologyDataRequest {
  title: string;
  subtitle: string;
  summary?: string;
  article_list?: IArticleListApiResponse[];
  lang: string;
  status: number;
}
export interface IAnthologyDataResponse {
  uid: string;
  title: string;
  subtitle: string;
  summary: string;
  article_list: IArticleListApiResponse[];
  studio: IStudioApiResponse;
  lang: string;
  status: number;
  childrenNumber: number;
  created_at: string;
  updated_at: string;
}
export interface IAnthologyResponse {
  ok: boolean;
  message: string;
  data: IAnthologyDataResponse;
}
export interface IAnthologyListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IAnthologyDataResponse[];
    count: number;
  };
}

export interface IAnthologyStudioListApiResponse {
  ok: boolean;
  message: string;
  data: {
    count: number;
    rows: IAnthologyStudioListDataApiResponse[];
  };
}
export interface IAnthologyStudioListDataApiResponse {
  count: number;
  studio: IStudioApiResponse;
}

export interface IArticleDataRequest {
  uid: string;
  title: string;
  subtitle: string;
  summary: string;
  content?: string;
  content_type?: string;
  status: number;
  lang: string;
}
export interface IChapterToc {
  book: number;
  paragraph: number;
  level: number;
  pali_title: string /**巴利文标题 */;
  title?: string /**译文文标题 */;
  progress?: number[];
}
export interface IArticleDataResponse {
  uid: string;
  title: string;
  subtitle: string;
  summary: string;
  content?: string;
  content_type?: string;
  toc?: IChapterToc[];
  html?: string;
  path?: ITocPathNode[];
  status: number;
  lang: string;
  anthology_count?: number;
  anthology_first?: { title: string };
  role?: TRole;
  studio?: IStudio;
  editor?: IUser;
  created_at: string;
  updated_at: string;
}
export interface IArticleResponse {
  ok: boolean;
  message: string;
  data: IArticleDataResponse;
}
export interface IArticleListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IArticleDataResponse[];
    count: number;
  };
}

export interface IArticleCreateRequest {
  title: string;
  lang: string;
  studio: string;
}

export interface IAnthologyCreateRequest {
  title: string;
  lang: string;
  studio: string;
}

export interface IArticleMapRequest {
  id?: string;
  collect_id?: string;
  article_id?: string;
  level: number;
  title: string;
  children?: number;
  deleted_at?: string;
}
export interface IArticleMapListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IArticleMapRequest[];
    count: number;
  };
}
export interface IArticleMapAddRequest {
  anthology_id: string;
  article_id: string[];
  operation: string;
}
export interface IArticleMapUpdateRequest {
  data: IArticleMapRequest[];
  operation: string;
}
export interface IArticleMapAddResponse {
  ok: boolean;
  message: string;
  data: number;
}
export interface IDeleteResponse {
  ok: boolean;
  message: string;
  data: number;
}
