import { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";

export interface IApiPaliChapterList {
  id: string;
  book: number;
  paragraph: number;
  level: number;
  toc: string;
  title: string;
  lenght: number;
  chapter_len: number;
  next_chapter: number;
  prev_chapter: number;
  parent: number;
  chapter_strlen: number;
  path: string;
}

export interface IApiResponsePaliChapterList {
  ok: boolean;
  message: string;
  data: { rows: IApiPaliChapterList[]; count: number };
}
export interface IApiResponsePaliChapter {
  ok: boolean;
  message: string;
  data: IApiPaliChapterList;
}

export interface IApiResponsePaliPara {
  book: number;
  paragraph: number;
  level: number;
  class: string;
  toc: string;
  text: string;
  html: string;
  lenght: number;
  chapter_len: number;
  next_chapter: number;
  prev_chapter: number;
  parent: number;
  chapter_strlen: number;
  path: string;
}

/**
 * progress?view=chapter_channels&book=98&par=22
 */
export interface IApiChapterChannels {
  book: number;
  para: number;
  uid: string;
  channel_id: string;
  progress: number;
  updated_at: string;
  views: number;
  likes: number[];
  channel: {
    type: string;
    owner_uid: string;
    editor_id: number;
    name: string;
    summary: string;
    lang: string;
    status: number;
    created_at: string;
    updated_at: string;
    uid: string;
  };
}

export interface IApiResponseChapterChannelList {
  ok: boolean;
  message: string;
  data: { rows: IApiChapterChannels[]; count: number };
}

export interface IApiChapterTag {
  id: string;
  name: string;
  count: number;
}
export interface IApiResponseChapterTagList {
  ok: boolean;
  message: string;
  data: { rows: IApiChapterTag[]; count: number };
}

export interface IApiResponseChannelListData {
  channel_id: string;
  count: number;
  channel: {
    id: number;
    type: string;
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
}
export interface IApiResponseChannelList {
  ok: boolean;
  message: string;
  data: { rows: IApiResponseChannelListData[]; count: number };
}

export interface ISentenceRequest {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channel: string;
  content: string;
}

export interface ISentenceData {
  book: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  content: string;
  html: string;
  editor: IUser;
  channel: IChannel;
  updated_at: string;
}

export interface ISentenceResponse {
  ok: boolean;
  message: string;
  data: ISentenceData;
}

export interface IPaliToc {
  book: number;
  paragraph: number;
  level: string;
  toc: string;
  translation?: string;
}

export interface IPaliTocListResponse {
  ok: boolean;
  message: string;
  data: { rows: IPaliToc[]; count: number };
}
