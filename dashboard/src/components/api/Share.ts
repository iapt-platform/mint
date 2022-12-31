import { IUser } from "../auth/User";
import { Role } from "./Auth";

export interface IShareData {
  id?: number;
  res_id: string;
  res_type: string;
  power?: number;
  res_name: string;
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
    role: Role;
    count: number;
  };
}
