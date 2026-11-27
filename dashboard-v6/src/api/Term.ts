import type { IStudio, IUser, TRole } from "./Auth";
import type { IChannel } from "./channel";
import type { LoaderFunctionArgs } from "react-router";
import { api, unwrap, unwrapQuiet } from "./client";
import { ApiError } from "./error";
import type { paths } from "./schema";

/* ─── v3（术语本身已迁 v3，取代 v2 terms 的 show / view=channel|studio|word / store / update / destroy）───
 * 词汇表已迁到 v3（`api/glossary.ts`），导入导出也已迁到 v3（见本文件下方）。
 */

type TermSingle =
  paths["/v3/terms/{term}"]["get"]["responses"][200]["content"]["application/json"];

/** v3 的一条术语：带 abilities、etag（修改时放进 If-Match） */
export type Term = NonNullable<TermSingle["data"]>;
export type TermLookupQuery = NonNullable<
  paths["/v3/terms"]["get"]["parameters"]["query"]
>;
export type TermListQuery = NonNullable<
  paths["/v3/channels/{channel}/terms"]["get"]["parameters"]["query"]
>;
export type StudioTermListQuery = NonNullable<
  paths["/v3/studios/{studio}/terms"]["get"]["parameters"]["query"]
>;
export type NewChannelTermInput =
  paths["/v3/channels/{channel}/terms"]["post"]["requestBody"]["content"]["application/json"];
export type NewStudioTermInput =
  paths["/v3/studios/{studio}/terms"]["post"]["requestBody"]["content"]["application/json"];
export type TermPatch = NonNullable<
  paths["/v3/terms/{term}"]["patch"]["requestBody"]
>["content"]["application/json"];

/** 词条质量 */
export const TERM_QUALITIES = [
  "featured",
  "standard",
  "draft",
  "pending",
] as const;
/** 正文方言：markdown 是旧方言，v3 新建默认 markdown-e2 */
export const TERM_CONTENT_TYPES = ["markdown-e2", "markdown"] as const;

export type TermShowQuery = NonNullable<
  paths["/v3/terms/{term}"]["get"]["parameters"]["query"]
>;

/**
 * 单条，带渲染后的「摘要 + 正文」（body）。query 可指定按哪些 channel 渲染、read / edit 模式。
 * quiet=true 时失败不弹提示（悬停浮层这类轻量调用用），只抛出。
 */
export const fetchTerm = async (
  term: string,
  query: TermShowQuery = {},
  quiet = false,
) =>
  (quiet ? unwrapQuiet : unwrap)(
    await api.GET("/v3/terms/{term}", { params: { path: { term }, query } }),
  ).data;

/** 按词查（只列看得见的）；with_body 时每条都带渲染后的正文。quiet=true 时失败不弹提示 */
export const lookupTerms = async (query: TermLookupQuery, quiet = false) =>
  (quiet ? unwrapQuiet : unwrap)(
    await api.GET("/v3/terms", { params: { query } }),
  );

export const fetchChannelTerms = async (
  channel: string,
  query: TermListQuery = {},
) =>
  unwrap(
    await api.GET("/v3/channels/{channel}/terms", {
      params: { path: { channel }, query },
    }),
  );

export const fetchStudioTerms = async (
  studio: string,
  query: StudioTermListQuery = {},
) =>
  unwrap(
    await api.GET("/v3/studios/{studio}/terms", {
      params: { path: { studio }, query },
    }),
  );

export const createChannelTerm = async (
  channel: string,
  body: NewChannelTermInput,
) =>
  unwrap(
    await api.POST("/v3/channels/{channel}/terms", {
      params: { path: { channel } },
      body,
    }),
  ).data;

export const createStudioTerm = async (
  studio: string,
  body: NewStudioTermInput,
) =>
  unwrap(
    await api.POST("/v3/studios/{studio}/terms", {
      params: { path: { studio } },
      body,
    }),
  ).data;

/**
 * 改术语。etag 取自读到的那一份（`term.etag`）：这条在你编辑期间被别人改过时返回 412，
 * unwrap 会弹出后端的提示并抛出，调用方不要静默重试（那会覆盖别人的修改）。
 */
export const updateTerm = async (term: string, etag: string, body: TermPatch) =>
  unwrap(
    await api.PATCH("/v3/terms/{term}", {
      params: { path: { term } },
      headers: { "If-Match": etag },
      body,
    }),
  ).data;

/** 软删除，返回被删的术语 */
export const deleteTerm = async (term: string) =>
  unwrap(await api.DELETE("/v3/terms/{term}", { params: { path: { term } } }))
    .data;

/* ─── 导入 / 导出（xlsx，取代 v2 terms-import / terms-export）─── */

export const TERM_XLSX =
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";

/** 导入到哪里：某个 channel，或某个 studio（用户或团队的 uuid） */
export type TermExchangeTarget = { channelId: string } | { studioId: string };

type TermImportResponse =
  paths["/v3/channels/{channel}/terms/imports"]["post"]["responses"][200]["content"]["application/json"];
export type TermImportResult = NonNullable<TermImportResponse["data"]>;

/** 导出成 xlsx：列表接口带 Accept: xlsx，整表返回（过滤同列表） */
export const exportTerms = async (
  target: TermExchangeTarget,
): Promise<Blob> => {
  const options = {
    headers: { Accept: TERM_XLSX },
    parseAs: "blob" as const,
  };
  const result =
    "channelId" in target
      ? await api.GET("/v3/channels/{channel}/terms", {
          params: { path: { channel: target.channelId } },
          ...options,
        })
      : await api.GET("/v3/studios/{studio}/terms", {
          params: { path: { studio: target.studioId } },
          ...options,
        });
  return unwrap(result) as unknown as Blob;
};

/**
 * 导入 xlsx。部分成功：出错的行跳过，`errors` 逐行给出原因（`row` 是表格行号）。
 */
export const importTerms = async (
  target: TermExchangeTarget,
  file: File,
): Promise<TermImportResult | undefined> => {
  const options = {
    // 规格里文件是 binary 字符串；实际以 FormData 上传
    body: { file: file as unknown as string },
    bodySerializer: (body: { file: string }) => {
      const form = new FormData();
      form.append("file", body.file as unknown as Blob);
      return form;
    },
  };
  const result =
    "channelId" in target
      ? await api.POST("/v3/channels/{channel}/terms/imports", {
          params: { path: { channel: target.channelId } },
          ...options,
        })
      : await api.POST("/v3/studios/{studio}/terms/imports", {
          params: { path: { studio: target.studioId } },
          ...options,
        });
  return unwrap(result).data;
};

/* ─── v2（仍在用的部分，见上方说明）─── */

export interface ITerm {
  id?: string;
  word?: string;
  tag?: string;
  meaning?: string;
  meaning2?: string[];
  note?: string;
  html?: string;
  summary?: string;
  summary_is_community?: boolean;
  channelId?: string;
  studioId?: string;
  lang?: string;
  save_as?: boolean;
  copy_channel?: string[];
  copy_lang?: string;
  pr?: boolean;
}

export interface ITermDataResponse {
  id: number;
  guid: string;
  word: string;
  tag: string;
  meaning: string;
  other_meaning: string;
  note: string | null;
  html?: string;
  channal: string;
  channel?: IChannel;
  studio: IStudio;
  editor: IUser;
  role?: TRole;
  exp?: number;
  language: string;
  community?: boolean;
  summary?: string;
  summary_is_community?: boolean;
  created_at: string;
  updated_at: string;
}

/** 路由 loader：面包屑只要 word */
export async function termLoader({ params }: LoaderFunctionArgs) {
  const termId = params.id;

  if (!termId) {
    throw new Response("Missing termId", { status: 400 });
  }
  try {
    return unwrapQuiet(
      await api.GET("/v3/terms/{term}", { params: { path: { term: termId } } }),
    ).data;
  } catch (e) {
    // 看不见（403）也当不存在；超时、网络不通、5xx 原样抛出，错误页按种类提示并可重试
    if (e instanceof ApiError && (e.status === 403 || e.status === 404)) {
      throw new Response("term not found", { status: 404 });
    }
    throw e;
  }
}
