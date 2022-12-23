export interface IDictDataRequest {
  id: number;
  word: string;
  type: string;
  grammar: string;
  mean: string;
  parent: string;
  note: string;
  factors: string;
  factormean: string;
  dictId?: string;
  dictName?: string;
  language: string;
  confidence: number;
}
export interface IApiResponseDictData {
  id: number;
  word: string;
  type: string;
  grammar: string;
  mean: string;
  parent: string;
  note: string;
  factors: string;
  factormean: string;
  language: string;
  dict_id: string;
  dict_name?: string;
  confidence: number;
  creator_id: number;
  updated_at: string;
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
  };
}
