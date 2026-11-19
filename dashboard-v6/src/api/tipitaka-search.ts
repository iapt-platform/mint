// src/api/tipitaka-search.ts
//
// workspace 搜索面板用到的 v3 检索接口：
// - 全文：`GET /v3/search`（OpenSearch，三藏段落）
// - 标题：`GET /v3/tipitaka-chapters?q=`（章节名定位）
import { api, unwrapQuiet } from "./client";
import type { paths } from "./schema";

/* ─── 全文 ─── */

type SearchOp = paths["/v3/search"]["get"];
type SearchQuery = SearchOp["parameters"]["query"];
type SearchBody = SearchOp["responses"][200]["content"]["application/json"];

export type SearchMode = NonNullable<SearchQuery["search_mode"]>;
export type SearchHit = NonNullable<SearchBody["data"]>[number];
export type SearchMeta = NonNullable<SearchBody["meta"]>;
export type FacetBucket = { key: string; count: number };

export interface IFullTextParams {
  q: string;
  mode: SearchMode;
  /** 分类（书的标签），须全部命中 */
  category: string[];
  page: number;
  perPage: number;
}

/**
 * 三藏段落全文检索，带分面计数。
 *
 * 只查巴利原文段落（resource_type=tipitaka、granularity=paragraph），
 * 分面只用 category（藏别 / 层级 / 书的标签混在一起，由调用方分组）。
 * 失败不弹全局提示（unwrapQuiet）：搜索面板自己显示错误，503 时也是。
 */
export const searchParagraphs = async (p: IFullTextParams) =>
  unwrapQuiet(
    await api.GET("/v3/search", {
      params: {
        query: {
          q: p.q,
          search_mode: p.mode,
          resource_type: "tipitaka",
          granularity: "paragraph",
          category: p.category.length ? p.category.join(",") : undefined,
          facets: true,
          page: p.page,
          per_page: p.perPage,
        },
      },
    }),
  );

/* ─── 标题 ─── */

type ChapterOp = paths["/v3/tipitaka-chapters"]["get"];
type ChapterQuery = NonNullable<ChapterOp["parameters"]["query"]>;
type ChapterBody = ChapterOp["responses"][200]["content"]["application/json"];

export type ChapterLayer = NonNullable<ChapterQuery["layer"]>;
export type ChapterHit = NonNullable<ChapterBody["data"]>[number];

export interface IChapterParams {
  q: string;
  layer?: ChapterLayer;
  page: number;
  perPage: number;
}

/** 按章节名找章节（巴利名或译名，容错拼写与变音符号） */
export const searchChapters = async (p: IChapterParams) =>
  unwrapQuiet(
    await api.GET("/v3/tipitaka-chapters", {
      params: {
        query: {
          q: p.q,
          layer: p.layer,
          page: p.page,
          per_page: p.perPage,
        },
      },
    }),
  );

/* ─── 定义（逐词表，按词形精确匹配） ─── */

type WordParagraphOp = paths["/v3/tipitaka-word-paragraphs"]["get"];
type WordParagraphBody =
  WordParagraphOp["responses"][200]["content"]["application/json"];
type WordBookBody =
  paths["/v3/tipitaka-word-paragraphs/aggregate"]["get"]["responses"][200]["content"]["application/json"];

export type WordParagraph = NonNullable<WordParagraphBody["data"]>[number];
export type WordParagraphMeta = NonNullable<WordParagraphBody["meta"]>;
export type WordBook = NonNullable<WordBookBody["data"]>[number];
export type WordBookMeta = NonNullable<WordBookBody["meta"]>;
export type BoldScope = NonNullable<
  NonNullable<WordParagraphOp["parameters"]["query"]>["bold"]
>;

export interface IWordParams {
  /** 词形（由 /v3/pali-word-forms 展开） */
  words: string[];
  bold?: BoldScope;
}

/** 命中这些词形的段落，按命中权重降序 */
export const searchWordParagraphs = async (
  p: IWordParams & { bookIds: number[]; page: number; perPage: number },
) =>
  unwrapQuiet(
    await api.GET("/v3/tipitaka-word-paragraphs", {
      params: {
        query: {
          words: p.words.join(","),
          bold: p.bold,
          book_id: p.bookIds.length ? p.bookIds.join(",") : undefined,
          page: p.page,
          per_page: p.perPage,
        },
      },
    }),
  );

/** 命中按书统计：每本书多少段、多少词次 */
export const aggregateWordBooks = async (p: IWordParams) =>
  unwrapQuiet(
    await api.GET("/v3/tipitaka-word-paragraphs/aggregate", {
      params: { query: { words: p.words.join(","), bold: p.bold } },
    }),
  );

/* ─── 页码（印本页码与 CS 段落号） ─── */

type PageNumberOp = paths["/v3/tipitaka-page-numbers/{page}"]["get"];
type PageNumberBody =
  PageNumberOp["responses"][200]["content"]["application/json"];

export type PageNumberHit = NonNullable<PageNumberBody["data"]>[number];
export type PageEdition = NonNullable<
  NonNullable<PageNumberOp["parameters"]["query"]>["edition"]
>;

/** 某个页码在各版本里的全部位置（不分页）；给了卷号就只要这一卷 */
export const searchPageNumbers = async (page: number, volume?: number) =>
  unwrapQuiet(
    await api.GET("/v3/tipitaka-page-numbers/{page}", {
      params: { path: { page }, query: { volume } },
    }),
  );
