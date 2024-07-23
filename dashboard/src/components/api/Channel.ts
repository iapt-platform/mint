import { IStudio } from "../auth/Studio";
import { TRole } from "./Auth";
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
