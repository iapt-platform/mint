import type { IStudio, IUser } from "./Auth";
import type { IChannel } from "./Channel";

export interface ISentHistoryData {
  id: string;
  sent_uid: string;
  content: string;
  editor: IUser;
  landmark: string;
  fork_from?: IChannel;
  fork_studio?: IStudio;
  pr_from?: string | null;
  accepter?: IUser;
  created_at: string;
}

export interface ISentHistoryListResponse {
  ok: boolean;
  message: string;
  data: { rows: ISentHistoryData[]; count: number };
}

export interface ISentHistory {
  content: string;
  editor: IUser;
  fork_from?: IChannel;
  pr_from?: string | null;
  accepter?: IUser;
  createdAt: string;
}
