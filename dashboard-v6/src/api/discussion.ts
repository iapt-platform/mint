import type { IUser } from "./Auth";

export type TResType =
  | "article"
  | "channel"
  | "chapter"
  | "sentence"
  | "wbw"
  | "term"
  | "task";
export type TDiscussionType = "qa" | "discussion" | "help" | "comment";

export interface IComment {
  id?: string; //id未提供为新建
  resId?: string;
  resType?: TResType;
  type: TDiscussionType;
  tplId?: string;
  user: IUser;
  parent?: string | null;
  title?: string;
  content?: string;
  html?: string;
  summary?: string;
  status?: "active" | "close";
  children?: IComment[];
  childrenCount?: number;
  newTpl?: boolean;
  createdAt?: string;
  updatedAt?: string;
}
