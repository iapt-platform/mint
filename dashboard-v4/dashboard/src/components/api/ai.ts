import { IStudio } from "../auth/Studio";
import { IUser } from "../auth/User";

export type TPrivacy = "private" | "public" | "disable";

export interface IKimiResponse {
  id: string;
  object: string;
  created: number;
  model: string;
  choices: AiChoice[];
  usage: AiUsage;
}

export interface AiUsage {
  prompt_tokens: number;
  completion_tokens: number;
  total_tokens: number;
}

export interface AiChoice {
  index: number;
  message: AiMessage;
  logprobs?: string | null; //volcengine
  finish_reason: string;
}

export interface AiMessage {
  role: string;
  content: string;
}

export interface IAiTranslateRequest {
  origin: string;
}

export interface IAiTranslateResponse {
  ok: boolean;
  message: string;
  data: IKimiResponse;
}

export interface IAiModel {
  uid: string;
  name: string;
  description?: string | null;
  url?: string | null;
  model?: string;
  key?: string;
  privacy: TPrivacy;
  owner: IStudio;
  editor: IUser;
  user: IUser;
  created_at: string;
  updated_at: string;
}

export interface IAiModelRequest {
  name: string;
  description?: string | null;
  system_prompt?: string | null;
  url?: string | null;
  model?: string;
  key?: string;
  privacy: TPrivacy;
  studio_name?: string;
}

export interface IAiModelListResponse {
  ok: boolean;
  message: string;
  data: { rows: IAiModel[]; total: number };
}

export interface IAiModelResponse {
  ok: boolean;
  message: string;
  data: IAiModel;
}

export interface IAiModelLogData {
  id: string;
  uid: string;
  model_id: string;
  request_headers: string;
  request_data: string;
  response_headers?: string | null;
  response_data?: string | null;
  status: number;
  success: boolean;
  request_at: string;
  created_at: string;
  updated_at: string;
}

export interface IAiModelLogListResponse {
  ok: boolean;
  message: string;
  data: { rows: IAiModelLogData[]; total: number };
}

export interface IAiModelSystem {
  view: string;
  models: string[];
}
