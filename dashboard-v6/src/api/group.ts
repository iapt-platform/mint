import type { LoaderFunctionArgs } from "react-router";
import { get } from "../request";
import type { IStudio, IUser, TRole } from "./Auth";

export interface IGroup {
  id: string;
  name: string;
}

export interface IGroupRequest {
  id?: string;
  name: string;
  description?: string;
  studio_name?: string;
}

export interface IGroupDataRequest {
  uid: string;
  name: string;
  description: string;
  owner: string;
  studio: IStudio;
  role: TRole;
  created_at: string;
  updated_at: string;
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
export interface IGroupMemberRequest {
  id?: number;
  user_id: string;
  group_id: string;
  power?: number;
  level?: number;
  status?: number;
}
export interface IGroupMemberData {
  id?: number;
  user_id: string;
  group_id: string;
  group: IStudio;
  power?: number;
  level?: number;
  status?: number;
  user: IUser;
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
export interface IDeleteResponse {
  ok: boolean;
  message: string;
  data: number;
}

export const fetchGroup = (groupId: string): Promise<IGroupResponse> => {
  return get<IGroupResponse>(`/api/v2/group/${groupId}`);
};

export async function groupLoader({ params }: LoaderFunctionArgs) {
  const teamId = params.teamId;

  if (!teamId) {
    throw new Response("Missing teamId", { status: 400 });
  }

  const res = await fetchGroup(teamId);

  if (!res.ok) {
    throw new Response("team not found", { status: 404 });
  }

  return res.data;
}
