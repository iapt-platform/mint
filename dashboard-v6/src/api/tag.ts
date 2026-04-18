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
