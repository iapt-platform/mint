export interface IPayload {
  res_type: string;
  res_id: string;
  book?: number;
  para_start?: number;
  para_end?: number;
  power: TPower;
}

export type TPower = "readonly" | "edit";

export interface ITokenCreate {
  payload: IPayload[];
}
export interface ITokenData {
  payload: IPayload;
  token: string;
}
export interface ITokenCreateResponse {
  ok: boolean;
  message: string;
  data: {
    rows: ITokenData[];
    count: number;
  };
}
