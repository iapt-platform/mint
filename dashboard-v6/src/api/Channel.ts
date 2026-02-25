import type { LoaderFunctionArgs } from "react-router";
import type { IStudio, TRole } from "./Auth";
import { get } from "../request";
export interface IChannel {
  id: string;
  name: string;
  type?: TChannelType;
  lang?: string;
}
export type TChannelType =
  | "translation"
  | "nissaya"
  | "original"
  | "wbw"
  | "commentary"
  | "similar";
export interface IChannelApiData {
  id: string;
  name: string;
  type?: TChannelType;
}

export interface ChannelInfoProps {
  channel: IChannelApiData;
  studio: IStudio;
  count?: number;
}
/**
 * 句子完成情况
 * [句子字符数，是否完成]
 *
 */
export type IFinal = [number, boolean];
export interface IApiResponseChannelData {
  uid: string;
  name: string;
  summary: string;
  type: TChannelType;
  studio: IStudio;
  lang: string;
  status: number;
  is_system: boolean;
  progress?: number;
  created_at: string;
  updated_at: string;
  role?: TRole;
  final?: IFinal[];
  content_created_at: string;
  content_updated_at: string;
}
export interface IApiResponseChannel {
  ok: boolean;
  message: string;
  data: IApiResponseChannelData;
}
export interface IApiResponseChannelList {
  ok: boolean;
  message: string;
  data: {
    rows: IApiResponseChannelData[];
    count: number;
  };
}

export interface ISentInChapterListResponse {
  ok: boolean;
  data: ISentInChapterListData;
  message: string;
}

export interface ISentInChapterListData {
  rows: ISentInChapterListDataRow[];
  count: number;
}

export interface ISentInChapterListDataRow {
  book: number;
  paragraph: number;
  word_begin: number;
  word_end: number;
}

export interface IResNumberResponse {
  ok: boolean;
  message: string;
  data: {
    my: number;
    collaboration: number;
  };
}

export interface IResNumber {
  translation?: number;
  nissaya?: number;
  commentary?: number;
  origin?: number;
  sim?: number;
}

export async function channelLoader({ params }: LoaderFunctionArgs) {
  const channelId = params.channelId;

  if (!channelId) {
    throw new Response("Missing channelId", { status: 400 });
  }

  const res = await get<IApiResponseChannel>(`/api/v2/channel/${channelId}`);

  if (!res.ok) {
    throw new Response("Channel not found", { status: 404 });
  }

  return res.data;
}
