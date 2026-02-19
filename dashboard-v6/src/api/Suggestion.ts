import type { IUser } from "./Auth";
import type { IChannelApiData } from "./Channel";

export interface ISuggestionCount {
  suggestion?: number;
  discussion?: number;
}
export interface ISuggestionData {
  id: string;
  uid: string;
  book: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  channel: IChannelApiData;
  content: string;
  html: string;
  editor: IUser;
  created_at: string;
  updated_at: string;
}
export interface ISuggestionResponse {
  ok: boolean;
  message: string;
  data: ISuggestionData;
}
export interface ISuggestionListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: ISuggestionData[];
    count: number;
  };
}
