/**
 * 一章有哪些 channel 及各自的完成度（阅读页的版本列表）。
 *
 * `GET /v3/tipitaka-chapter-channels/{book}/{para}`，取代 v2 的
 * `progress?view=chapter_channels`。只列当前用户看得见的 channel。
 */
import { api, unwrap } from "./client";
import type { paths } from "./schema";

type ChapterChannelList =
  paths["/v3/tipitaka-chapter-channels/{book}/{para}"]["get"]["responses"][200]["content"]["application/json"];

export type ChapterChannel = NonNullable<ChapterChannelList["data"]>[number];

/** 单章最多几十个 channel，一页 200 足够取全。 */
const PAGE_SIZE = 200;

export const fetchChapterChannels = async (
  book: number,
  para: number
): Promise<ChapterChannel[]> =>
  unwrap(
    await api.GET("/v3/tipitaka-chapter-channels/{book}/{para}", {
      params: { path: { book, para }, query: { per_page: PAGE_SIZE } },
    })
  ).data ?? [];
