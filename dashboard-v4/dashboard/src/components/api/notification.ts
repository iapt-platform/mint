import { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";

export interface INotificationPutResponse {
  ok: boolean;
  data: {
    unread: number;
  };
  message: string;
}

export interface INotificationListResponse {
  ok: boolean;
  data: INotificationListData;
  message: string;
}

export interface INotificationListData {
  rows: INotificationData[];
  count: number;
  unread: number;
}

interface INotificationData {
  id: string;
  from: IUser;
  to: IUser;
  channel: IChannel;
  url?: string;
  title?: string;
  book_title?: string;
  content: string;
  content_type: string;
  res_type: string;
  res_id: string;
  status: string;
  deleted_at?: string;
  created_at: string;
  updated_at: string;
}
export interface INotificationRequest {
  status: string;
}
