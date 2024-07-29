import { IStudio } from "../auth/Studio";
import { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";
import { TContentType } from "../discussion/DiscussionCreate";
import { ISuggestionCount, IWidgetSentEditInner } from "../template/SentEdit";
import { TChannelType } from "./Channel";
import { TagNode } from "./Tag";

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
  progress_line?: number[];
}

export interface IPaliChapterListResponse {
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
export interface IChapterChannelData {
  book: number;
  para: number;
  uid: string;
  channel_id: string;
  progress: number;
  progress_line?: number[];
  updated_at: string;
  views: number;
  likes: number[];
  channel: {
    type: TChannelType;
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
  studio: IStudio;
}

export interface IChapterChannelListResponse {
  ok: boolean;
  message: string;
  data: { rows: IChapterChannelData[]; count: number };
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

export interface ISentenceDiffRequest {
  sentences: string[];
  channels: string[];
}
export interface ISentenceDiffData {
  book_id: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  channel_uid: string;
  content: string | null;
  content_type: string;
  editor_uid: string;
  updated_at: string;
}
export interface ISentenceDiffResponse {
  ok: boolean;
  message: string;
  data: { rows: ISentenceDiffData[]; count: number };
}

export interface ISentenceRequest {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channel: string;
  content: string | null;
  contentType?: TContentType;
  prEditor?: string;
  prId?: string;
  prUuid?: string;
  prEditAt?: string;
  channels?: string;
  html?: boolean;
}

export interface ISentenceData {
  id?: string;
  book: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  content: string;
  content_type?: TContentType;
  html: string;
  editor: IUser;
  channel: IChannel;
  studio: IStudio;
  updated_at: string;
  acceptor?: IUser;
  pr_edit_at?: string;
  fork_at?: string;
  suggestionCount?: ISuggestionCount;
}

export interface ISentenceResponse {
  ok: boolean;
  message: string;
  data: ISentenceData;
}
export interface ISentenceListResponse {
  ok: boolean;
  message: string;
  data: { rows: ISentenceData[]; count: number };
}
export interface ISentenceNewRequest {
  sentences: ISentenceDiffData[];
  channel?: string;
  copy?: boolean;
  fork_from?: string;
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

export interface IPaliBookListResponse {
  name: string;
  tag: string[];
  children?: IPaliBookListResponse[];
}

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

export interface ILangList {
  lang: string;
  count: number;
}
export interface IChapterLangListResponse {
  ok: boolean;
  message: string;
  data: { rows: ILangList[]; count: number };
}

export interface ISentencePrRequest {
  book?: number;
  para?: number;
  begin?: number;
  end?: number;
  channel?: string;
  text: string;
}
export interface ISentencePrResponseData {
  book_id: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  channel_uid: string;
}
export interface ISentencePrResponse {
  ok: boolean;
  message: string;
  data: {
    new: ISentencePrResponseData;
    count: number;
    webhook: { message: string; ok: boolean };
  };
}

export interface ISimSent {
  sent: string;
  sim: number;
}
export interface ISentenceSimListResponse {
  ok: boolean;
  message: string;
  data: { rows: ISimSent[]; count: number };
}

export interface ISentenceWbwListResponse {
  ok: boolean;
  message: string;
  data: { rows: IWidgetSentEditInner[]; count: number };
}

export interface IEditableSentence {
  ok: boolean;
  message: string;
  data: IWidgetSentEditInner;
}
