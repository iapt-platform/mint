import { IStudioApiResponse, IUserRequest, TRole } from "./Auth";

export interface IGroupRequest {
  name: string;
  studio_name: string;
}

export interface IGroupDataRequest {
  uid: string;
  name: string;
  description: string;
  studio: IStudioApiResponse;
  role: TRole;
  created_at: string;
}
export interface IGroupResponse {
  ok: boolean;
  message: string;
  data: IGroupDataRequest;
}
export interface IGroupListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IGroupDataRequest[];
    count: number;
  };
}

export interface IGroupMemberData {
  id?: number;
  user_id: string;
  group_id: string;
  power?: number;
  level?: number;
  status?: number;
  user?: IUserRequest;
  created_at?: string;
  updated_at?: string;
}
export interface IGroupMemberResponse {
  ok: boolean;
  message: string;
  data: IGroupMemberData;
}
export interface IGroupMemberListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IGroupMemberData[];
    role: TRole;
    count: number;
  };
}

export interface IGroupMemberDeleteResponse {
  ok: boolean;
  message: string;
  data: boolean;
}
