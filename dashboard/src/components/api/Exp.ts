export interface IUserOperationDailyRequest {
  date_int: number;
  duration: number;
  hit?: number;
}

export interface IUserOperationDailyResponse {
  ok: boolean;
  message: string;
  data: { rows: IUserOperationDailyRequest[]; count: number };
}
