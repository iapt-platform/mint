import { IUserApiResponse } from "./Auth";
import { IChannelApiData } from "./Channel";

export interface ISuggestionData {
  id: string;
  book: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  channel: IChannelApiData;
  content: string;
  html: string;
  editor: IUserApiResponse;
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