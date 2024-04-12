import { IStudio } from "../auth/Studio";
import { IUser } from "../auth/User";
import { ICaseListData } from "../dict/CaseList";

export interface IDictRequest {
  id?: number;
  word: string;
  type?: string | null;
  grammar?: string | null;
  mean?: string | null;
  parent?: string | null;
  parent2?: string | null;
  note?: string | null;
  factors?: string | null;
  factormean?: string | null;
  confidence: number;
  dict_id?: string;
  dict_name?: string;
  language?: string;
  creator_id?: number;
  editor?: IUser;
  studio?: IStudio;
  status?: number;
  updated_at?: string;
}
export interface IUserDictCreate {
  data: string;
  view: string;
}
export interface IDictResponse {
  ok: boolean;
  message: string;
  data: number[];
}
export interface IDictInfo {
  id: string;
  name: string;
  shortname: string;
}
export interface IApiResponseDictData {
  id: string;
  word: string;
  type?: string | null;
  grammar?: string | null;
  mean?: string | null;
  parent?: string | null;
  note?: string | null;
  factors?: string | null;
  factormean?: string | null;
  source: string | null;
  language: string;
  dict?: IDictInfo;
  dict_id: string;
  dict_name?: string;
  dict_shortname?: string;
  shortname?: string;
  confidence: number;
  creator_id: number;
  updated_at: string;
  exp?: number;
  editor?: IUser;
  status?: number;
}
export interface IApiResponseDict {
  ok: boolean;
  message: string;
  data: IApiResponseDictData;
}
export interface IApiResponseDictList {
  ok: boolean;
  message: string;
  data: {
    rows: IApiResponseDictData[];
    count: number;
    time?: number;
  };
}

export interface IVocabularyData {
  word: string;
  count: number;
  meaning?: string;
  strlen: number;
}
export interface IVocabularyListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IVocabularyData[];
    count: number;
  };
}

export interface IUserDictDeleteRequest {
  id: string;
}

export interface ICaseItem {
  word: string;
  case: ICaseListData[];
  count: number;
}
export interface ICaseListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: ICaseItem[];
    count: number;
  };
}

export interface IFirstMeaning {
  word?: string;
  meaning?: string;
}
export interface IDictFirstMeaningResponse {
  ok: boolean;
  message: string;
  data: IFirstMeaning[];
}
