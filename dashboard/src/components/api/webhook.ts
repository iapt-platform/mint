import { IUser } from "../auth/User";
import { TResType } from "../discussion/DiscussionListCard";

export type TReceiverType = "wechat" | "dingtalk";

export interface IWebhookRequest {
  res_type: TResType;
  res_id: string;
  url: string;
  receiver: TReceiverType;
  event?: string[] | null;
  status?: string;
}

export interface IWebhookApiData {
  id: string;
  res_type: TResType;
  res_id: string;
  url: string;
  receiver: TReceiverType;
  event: string[] | null;
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