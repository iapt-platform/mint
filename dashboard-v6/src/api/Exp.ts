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

export interface IUserStatistic {
  exp: { sum: number };
  wbw: { count: number };
  lookup: { count: number };
  translation: { count: number; count_pub: number };
  term: { count: number; count_with_note: number };
  dict: { count: number };
}
export interface IUserStatisticResponse {
  ok: boolean;
  message: string;
  data: IUserStatistic;
}
