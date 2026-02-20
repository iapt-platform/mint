import type { IUser } from "./Auth";
import type { TResType } from "./discussion";

export type TReceiverType = "wechat" | "dingtalk";

export interface IWebhookEvent {
  key: string;
  tpl?: string;
  tplTitle?: string;
}

export interface IWebhookRequest {
  res_type: TResType;
  res_id: string;
  url: string;
  receiver: TReceiverType;
  event?: string[] | null;
  event2?: IWebhookEvent[] | null;
  status?: string;
}

export interface IWebhookApiData {
  id: string;
  res_type: TResType;
  res_id: string;
  url: string;
  receiver: TReceiverType;
  event: string[] | null;
  event2?: IWebhookEvent[] | null;
  fail: number;
  success: number;
  status: string;
  editor: IUser;
  created_at: string | null;
  updated_at: string | null;
}

export interface IWebhookResponse {
  ok: boolean;
  message: string;
  data: IWebhookApiData;
}
export interface IWebhookListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IWebhookApiData[];
    count: number;
  };
}
