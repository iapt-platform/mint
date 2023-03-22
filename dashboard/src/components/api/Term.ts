import { IStudio } from "../auth/StudioName";
import { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";

export interface ITermDataRequest {
  id?: string;
  word: string;
  tag?: string;
  meaning: string;
  other_meaning?: string;
  note?: string;
  channal?: string;
  studioName?: string;
  language: string;
}
export interface ITermDataResponse {
  id: number;
  guid: string;
  word: string;
  tag: string;
  meaning: string;
  other_meaning: string;
  note: string;
  channal: string;
  channel?: IChannel;
  studio: IStudio;
  editor: IUser;
  language: string;
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
