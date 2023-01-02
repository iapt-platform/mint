import { ITocPathNode } from "../corpus/TocPath";
import type { IStudioApiResponse } from "./Auth";

export interface ICourseListApiResponse {
  article: string;
  title: string;
  level: string;
  children: number;
}

export interface ICourseDataRequest {
  uid: string;
  title: string;
  subtitle: string;
  summary: string;
  content: string;
  content_type: string;
  status: number;
  lang: string;
}
export interface ICourseDataResponse {
  uid: string;
  title: string;
  subtitle: string;
  summary: string;
  content: string;
  content_type: string;
  path?: ITocPathNode[];
  status: number;
  lang: string;
  created_at: string;
  updated_at: string;
}
export interface ICourseResponse {
  ok: boolean;
  message: string;
  data: ICourseDataResponse;
}
export interface ICourseListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: ICourseDataResponse[];
    count: number;
  };
}

export interface ICourseCreateRequest {
  title: string;
  lang: string;
  studio: string;
}

export interface IAnthologyCreateRequest {
  title: string;
  lang: string;
  studio: string;
}
