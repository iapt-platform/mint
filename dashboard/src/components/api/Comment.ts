import { IUserRequest } from "./Auth";

export interface ICommentRequest {
  id?: string;
  content?: string;
  editor?: IUserRequest;
  updated_at?: string;
}

export interface ICommentResponse {
  ok: boolean;
  message: string;
  data: ICommentRequest;
}

export interface ICommentListResponse {
  ok: boolean;
  message: string;
  data: { rows: ICommentRequest[]; count: number };
}
