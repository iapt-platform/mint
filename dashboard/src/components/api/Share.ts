import { IUser } from "../auth/User";
import { IGroup } from "../group/Group";
import { TRole } from "./Auth";

export interface IShareRequest {
  res_id: string;
  res_type: number;
  role: TRole;
  user_id: string[];
  user_type: string;
}
export interface IShareUpdateRequest {
  role: TRole;
}
export interface IShareData {
  id?: string;
  res_id: string;
  res_type: string;
  power?: number;
  res_name: string;
  user?: IUser;
  group?: IGroup;
  owner?: IUser;
  role?: TRole;
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
export interface IShareDeleteResponse {
  ok: boolean;
  message: string;
  data: number;
}
