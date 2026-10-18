import type { LoaderFunctionArgs } from "react-router";
import { get } from "../request";
import type { IStudio, IUser } from "./Auth";

export interface TagNode {
  id: string;
  name: string;
  description?: string;
}

export interface ITagRequest {
  id?: string;
  name?: string;
  description?: string | null;
  color?: number;
  studio?: string;
  created_at?: string;
  updated_at?: string;
}
export interface ITag {
  id?: string;
  name?: string;
  description?: string | null;
  color?: number;
  owner?: IStudio;
  created_at?: string;
  updated_at?: string;
}
export interface ITagData {
  id: string;
  name: string;
  description?: string | null;
  color: number;
  owner?: IStudio;
  created_at: string;
  updated_at: string;
}

export interface ITagResponse {
  ok: boolean;
  message: string;
  data: ITagData;
}

export interface ITagResponseList {
  ok: boolean;
  message: string;
  data: { rows: ITagData[]; count: number };
}

export interface ITagMapRequest {
  id?: string;
  table_name?: string;
  anchor_id?: string;
  tag_id?: string;
  studio?: string;
  course?: string;
}

export interface ITagMapData {
  id: string;
  table_name: string;
  anchor_id: string;
  tag_id: string;
  name?: string | null;
  color?: number | null;
  description?: string | null;
  title?: string;
  editor?: IUser;
  owner?: IStudio;
  created_at?: string;
  updated_at?: string;
}

export interface ITagMapResponse {
  ok: boolean;
  message: string;
  data: ITagMapData;
}

export interface ITagMapResponseList {
  ok: boolean;
  message: string;
  data: { rows: ITagMapData[]; count: number };
}

export const fetchTag = (tagId: string): Promise<ITagResponse> => {
  return get<ITagResponse>(`/api/v2/tag/${tagId}`);
};

export async function tagLoader({ params }: LoaderFunctionArgs) {
  const tagId = params.tagId;

  if (!tagId) {
    throw new Response("Missing tagId", { status: 400 });
  }

  const res = await fetchTag(tagId);

  if (!res.ok) {
    throw new Response("tag not found", { status: 404 });
  }

  return res.data;
}

/**
 * 页面上一批句子的标签：`POST /v2/discussion-count` 的请求与响应（只用到 tags）。
 *
 * FIXME: 这个 v2 端点同时返回讨论明细（discussions），讨论角标已改走 v3 batch-count，
 * 这里只读 tags。标签迁 v3 时换成标签自己的批量端点，这两个类型随之删掉。
 */
export interface ISentenceTagsRequest {
  /** 句子坐标 [book, paragraph, word_start, word_end] */
  sentences: string[][];
}
export interface ISentenceTagsResponse {
  ok: boolean;
  message: string;
  data: { tags: ITagMapData[] };
}
