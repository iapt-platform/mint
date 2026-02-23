import type { ITag } from "./Tag";
export interface IFtsData {
  book: number;
  paragraph: number;
  title?: string;
  paliTitle: string;
  pcdBookId: number;
  count: number;
  tags?: ITag[];
}
export interface IFtsResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IFtsData[];
    count: number;
  };
}
