import { IStudio } from "../auth/Studio";
import { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";
import { TRole } from "./Auth";

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
