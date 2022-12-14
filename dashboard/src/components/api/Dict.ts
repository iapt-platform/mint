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
  language: string;
  confidence: number;
}
export interface IApiResponseDictlData {
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
  confidence: number;
  creator_id: number;
  updated_at: string;
}
export interface IApiResponseDict {
  ok: boolean;
  message: string;
  data: IApiResponseDictlData;
}
export interface IApiResponseDictList {
  ok: boolean;
  message: string;
  data: {
    rows: IApiResponseDictlData[];
    count: number;
  };
}
