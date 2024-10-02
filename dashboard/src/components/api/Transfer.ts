import { IStudio } from "../auth/Studio";
import { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";
import { TResType } from "../discussion/DiscussionListCard";

export type ITransferStatus = "transferred" | "accept" | "refuse" | "cancel";
export interface ITransferRequest {
  res_type?: TResType;
  res_id?: string[];
  new_owner?: string;
  status?: ITransferStatus;
}
export interface ITransferResponseData {
  id: string;
  origin_owner: IStudio;
  res_type: TResType;
  res_id: string;
  channel?: IChannel;
  transferor: IUser;
  new_owner: IStudio;
  status: ITransferStatus;
  editor?: IUser | null;
  created_at: string;
  updated_at: string;
}
export interface ITransferCreateResponse {
  ok: boolean;
  message: string;
  data: number;
}
export interface ITransferResponse {
  ok: boolean;
  message: string;
  data: ITransferResponseData;
}
export interface ITransferResponseList {
  ok: boolean;
  message: string;
  data: {
    rows: ITransferResponseData[];
    count: number;
    out: number;
    in: number;
  };
}
