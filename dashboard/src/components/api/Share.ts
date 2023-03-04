import { IUser } from "../auth/User";
import { TRole } from "./Auth";

export interface IShareRequest {
  res_id: string;
  res_type: string;
  role: TRole;
  user_id: string;
  user_type: string;
}

export interface IShareData {
  id?: number;
  res_id: string;
  res_type: string;
  power?: number;
  res_name: string;
  user?: IUser;
  owner?: IUser;
  created_at?: string;
  updated_at?: string;
}
export interface IShareResponse {
  ok: boolean;
  message: string;
  data: IShareData;
}
export interface IShareListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IShareData[];
    role: TRole;
    count: number;
  };
}
