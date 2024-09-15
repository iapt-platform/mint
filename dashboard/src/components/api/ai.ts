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
