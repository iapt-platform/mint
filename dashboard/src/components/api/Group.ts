import { IStudioApiResponse } from "./Auth";

export type Role = "owner" | "manager" | "editor" | "member";

export interface IGroupDataRequest {
	uid: string;
	name: string;
	description: string;
	studio: IStudioApiResponse;
	role: Role;
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
