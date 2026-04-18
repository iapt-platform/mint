import type { IStudio, IUser, TRole } from "./Auth";
import type { IChannel } from "./channel";
import { get } from "../request";
import type { LoaderFunctionArgs } from "react-router";

export interface ITerm {
  id?: string;
  word?: string;
  tag?: string;
  meaning?: string;
  meaning2?: string[];
  note?: string;
  html?: string;
  summary?: string;
  summary_is_community?: boolean;
  channelId?: string;
  studioId?: string;
  lang?: string;
  save_as?: boolean;
  copy_channel?: string[];
  copy_lang?: string;
  pr?: boolean;
}

export interface ITermDataRequest {
  id?: string;
  word: string;
  tag?: string;
  meaning: string;
  other_meaning?: string;
  note?: string;
  channel?: string;
  studioName?: string;
  studioId?: string;
  language?: string;
  parent_channel_id?: string;
  save_as?: boolean;
  copy_channel?: string;
  copy_lang?: string;
  pr?: boolean;
}

export interface ITermDataResponse {
  id: number;
  guid: string;
  word: string;
  tag: string;
  meaning: string;
  other_meaning: string;
  note: string | null;
  html?: string;
  channal: string;
  channel?: IChannel;
  studio: IStudio;
  editor: IUser;
  role?: TRole;
  exp?: number;
  language: string;
  community?: boolean;
  summary?: string;
  summary_is_community?: boolean;
  created_at: string;
  updated_at: string;
}

export interface ITermResponse {
  ok: boolean;
  message: string;
  data: ITermDataResponse;
}

export interface ITermListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: ITermDataResponse[];
    count: number;
  };
}

interface IMeaningCount {
  meaning: string;
  count: number;
}

interface IStudioChannel {
  name: string;
  uid: string;
}

export interface ITermCreate {
  word: string;
  meaningCount: IMeaningCount[];
  studioChannels: IStudioChannel[];
  language: string;
  studio: IStudio;
}

export interface ITermCreateResponse {
  ok: boolean;
  message: string;
  data: ITermCreate;
}

export interface ITermDeleteRequest {
  uuid: boolean;
  id: string[];
}

// ---------- API ----------

export interface IGetTermParams {
  id: string;
  mode?: "read" | "edit";
  channelsId?: string | null;
}

export function getTerm({
  id,
  mode,
  channelsId,
}: IGetTermParams): Promise<ITermResponse> {
  let url = `/api/v2/terms/${id}?a=a`;
  if (mode) {
    url += `&mode=${mode}`;
  }
  if (channelsId) {
    url += `&channel=${channelsId}`;
  }

  return get<ITermResponse>(url);
}

export async function termLoader({ params }: LoaderFunctionArgs) {
  const termId = params.id;

  if (!termId) {
    throw new Response("Missing termId", { status: 400 });
  }

  const res = await getTerm({ id: termId });

  if (!res.ok) {
    throw new Response("term not found", { status: 404 });
  }

  return res.data;
}
