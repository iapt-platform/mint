import { get } from "../request";

/** 单条句子 */
export interface ISimSent {
  sent: string;
  sim: number;
}

/** 后端返回结构 */
export interface ISentenceSimListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: ISimSent[];
    count: number;
  };
}

export interface ISentSimParams {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  limit: number;
  offset: number;
  sim: number;
  channelsId?: string[];
}

export async function fetchSentSim(
  params: ISentSimParams
): Promise<ISentenceSimListResponse> {
  const { book, para, wordStart, wordEnd, limit, offset, sim, channelsId } =
    params;

  let url = `/v2/sent-sim?view=sentence&book=${book}&paragraph=${para}&start=${wordStart}&end=${wordEnd}&mode=edit`;
  url += `&limit=${limit}`;
  url += `&offset=${offset}`;
  url += `&sim=${sim}`;
  url += channelsId ? `&channels=${channelsId.join()}` : "";

  return get<ISentenceSimListResponse>(url);
}
