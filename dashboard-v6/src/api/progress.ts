import type { IStudio } from "./Auth";
import type { TChannelType } from "./channel";
import type { TagNode } from "./tag";

export interface IApiResponseChannelListData {
  channel_id: string;
  count: number;
  channel: {
    id: number;
    type: TChannelType;
    owner_uid: string;
    editor_id: number;
    name: string;
    summary: string;
    lang: string;
    status: number;
    setting: string;
    created_at: string;
    updated_at: string;
    uid: string;
  };
  studio: IStudio;
}
export interface IApiResponseChannelList {
  ok: boolean;
  message: string;
  data: { rows: IApiResponseChannelListData[]; count: number };
}

//=========

export interface IChapterData {
  title: string;
  toc: string;
  book: number;
  para: number;
  path: string;
  tags: TagNode[];
  channel: { name: string; owner_uid: string };
  studio: IStudio;
  channel_id: string;
  summary: string;
  view: number;
  like: number;
  status?: number;
  progress: number;
  progress_line?: number[];
  created_at: string;
  updated_at: string;
}
export interface IChapterListResponse {
  ok: boolean;
  message: string;
  data: { rows: IChapterData[]; count: number };
}

//===========

export interface ILangList {
  lang: string;
  count: number;
}
export interface IChapterLangListResponse {
  ok: boolean;
  message: string;
  data: { rows: ILangList[]; count: number };
}
