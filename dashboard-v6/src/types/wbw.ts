import type { IUser } from "../api/Auth";

export type TFieldName =
  | "word"
  | "real"
  | "meaning"
  | "type"
  | "grammar"
  | "grammar2"
  | "case"
  | "parent"
  | "parent2"
  | "factors"
  | "factorMeaning"
  | "relation"
  | "note"
  | "bookMarkColor"
  | "bookMarkText"
  | "locked"
  | "attachments"
  | "confidence";

export interface IWbwField {
  field: TFieldName;
  value: string;
}

export enum WbwStatus {
  initiate = 0,
  auto = 3,
  apply = 5,
  manual = 7,
}

export interface IWbwAttachment {
  id: string;
  content_type: string;
  size: number;
  title: string;
}
export interface WbwElement<R> {
  value: R;
  status: WbwStatus;
}

export interface IWbw {
  uid?: string;
  book: number;
  para: number;
  sn: number[];
  word: WbwElement<string>;
  real: WbwElement<string | null>;
  meaning?: WbwElement<string | null>;
  type?: WbwElement<string | null>;
  grammar?: WbwElement<string | null>;
  style?: WbwElement<string | null>;
  case?: WbwElement<string | null>;
  parent?: WbwElement<string | null>;
  parent2?: WbwElement<string | null>;
  grammar2?: WbwElement<string | null>;
  factors?: WbwElement<string | null>;
  factorMeaning?: WbwElement<string | null>;
  relation?: WbwElement<string | null>;
  note?: WbwElement<string | null>;
  bookMarkColor?: WbwElement<number | null>;
  bookMarkText?: WbwElement<string | null>;
  locked?: boolean;
  confidence: number;
  attachments?: IWbwAttachment[];
  hasComment?: boolean;
  grammarId?: string;
  bookName?: string;
  editor?: IUser;
  created_at?: string;
  updated_at?: string;
}
export interface IWbwFields {
  real?: boolean;
  meaning?: boolean;
  factors?: boolean;
  factorMeaning?: boolean;
  factorMeaning2?: boolean;
  case?: boolean;
}

export type TWbwDisplayMode = "block" | "inline" | "list";
