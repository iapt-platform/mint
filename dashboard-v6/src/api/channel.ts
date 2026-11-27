// src/api/channel.ts
import type { LoaderFunctionArgs } from "react-router";
import type { IStudio, TRole } from "./Auth";
import { get } from "../request";
import { api, unwrap, unwrapQuiet } from "./client";
import { ApiError } from "./error";
import type { paths } from "./schema";

/* ─── v3（channel 本身已迁 v3，取代 v2 channel / channel-my-number / channel-progress）─── */

type ChannelSingle =
  paths["/v3/channels/{channel}"]["get"]["responses"][200]["content"]["application/json"];
type ChannelProgressList =
  paths["/v3/channels/batch-progress"]["post"]["responses"][200]["content"]["application/json"];

/** v3 的一个 channel：拥有者可以是个人或团队，带当前用户的 abilities */
export type Channel = NonNullable<ChannelSingle["data"]>;
export type ChannelAbilities = NonNullable<Channel["abilities"]>;
export type ChannelProgress = NonNullable<ChannelProgressList["data"]>[number];
export type PublicChannelQuery = NonNullable<
  paths["/v3/channels"]["get"]["parameters"]["query"]
>;
export type StudioChannelQuery = NonNullable<
  paths["/v3/studios/{studio}/channels"]["get"]["parameters"]["query"]
>;
export type EditableChannelQuery = NonNullable<
  paths["/v3/channels/editable"]["get"]["parameters"]["query"]
>;
export type NewChannelInput =
  paths["/v3/studios/{studio}/channels"]["post"]["requestBody"]["content"]["application/json"];
export type ChannelInput = NonNullable<
  paths["/v3/channels/{channel}"]["patch"]["requestBody"]
>["content"]["application/json"];

/** 列表里显示的身份：能删 = 拥有者，能改信息 = 管理员，能写 = 编辑，否则只读成员 */
export const channelRole = (abilities?: ChannelAbilities): TRole =>
  abilities?.delete
    ? "owner"
    : abilities?.update
      ? "manager"
      : abilities?.write
        ? "editor"
        : "member";

/** 拥有者摘要 → 现有 Studio 组件吃的 IStudio */
export const channelOwnerStudio = (
  owner?: Channel["owner"],
): IStudio | undefined =>
  owner
    ? {
        id: owner.id ?? "",
        nickName: owner.nickName,
        realName: owner.realName,
        studioName: owner.realName,
        avatar: owner.avatar ?? undefined,
      }
    : undefined;

export const fetchPublicChannels = async (query: PublicChannelQuery = {}) =>
  unwrap(await api.GET("/v3/channels", { params: { query } }));

export const fetchStudioChannels = async (
  studio: string,
  query: StudioChannelQuery = {},
) =>
  unwrap(
    await api.GET("/v3/studios/{studio}/channels", {
      params: { path: { studio }, query },
    }),
  );

export const fetchEditableChannels = async (query: EditableChannelQuery = {}) =>
  unwrap(await api.GET("/v3/channels/editable", { params: { query } }));

export const createChannel = async (studio: string, body: NewChannelInput) =>
  unwrap(
    await api.POST("/v3/studios/{studio}/channels", {
      params: { path: { studio } },
      body,
    }),
  ).data;

export const updateChannel = async (channel: string, body: ChannelInput) =>
  unwrap(
    await api.PATCH("/v3/channels/{channel}", {
      params: { path: { channel } },
      body,
    }),
  ).data;

/** 软删除；channel 下还有内容时后端 409，原因由 unwrap 弹出 */
export const deleteChannel = async (channel: string) =>
  unwrap(
    await api.DELETE("/v3/channels/{channel}", {
      params: { path: { channel } },
    }),
  ).data;
export interface IChannel {
  id: string;
  name: string;
  type?: TChannelType;
  lang?: string;
}
export type TChannelType =
  "translation" | "nissaya" | "original" | "wbw" | "commentary" | "similar";
export interface IChannelApiData {
  id: string;
  name: string;
  type?: TChannelType;
}

export type TChannelSourceType = "original" | "reprint" | "ai";
export const SOURCE_TYPE_OPTIONS: TChannelSourceType[] = [
  "original",
  "reprint",
  "ai",
];
/**
 * 句子完成情况
 * [句子字符数，是否完成]
 *
 */
export type IFinal = [number, boolean];
export interface ISentInChapterListResponse {
  ok: boolean;
  data: ISentInChapterListData;
  message: string;
}

export interface ISentInChapterListData {
  rows: ISentInChapterListDataRow[];
  count: number;
}

export interface ISentInChapterListDataRow {
  book: number;
  paragraph: number;
  word_begin: number;
  word_end: number;
}

export interface IResNumber {
  translation?: number;
  nissaya?: number;
  commentary?: number;
  origin?: number;
  sim?: number;
}

export interface IChannelItem {
  id: number;
  uid: string;
  title: string;
  summary: string;
  type: TChannelType;
  studio: IStudio;
  shareType: string;
  role?: string;
  publicity: number;
  final?: IFinal[];
  progress: number;
  createdAt: number;
  content_created_at?: string;
  content_updated_at?: string;
}

export async function channelLoader({ params }: LoaderFunctionArgs) {
  const channel = params.channelId;
  if (!channel) {
    throw new Response("Missing channelId", { status: 400 });
  }
  try {
    return unwrapQuiet(
      await api.GET("/v3/channels/{channel}", {
        params: { path: { channel } },
      }),
    ).data;
  } catch (e) {
    // 看不见（v3 回 403）也当不存在；超时、网络不通、5xx 原样抛出，错误页按种类提示并可重试
    if (e instanceof ApiError && (e.status === 403 || e.status === 404)) {
      throw new Response("Channel not found", { status: 404 });
    }
    throw e;
  }
}

/**
 * 按 uid 取一个 channel。失败**抛出** ApiError、不弹提示（quiet）：
 * 页面级的调用显示错误页，只拿来显示名字的调用自己 catch 回退。
 */
export const fetchChannel = async (channel: string): Promise<Channel> =>
  unwrapQuiet(
    await api.GET("/v3/channels/{channel}", {
      params: { path: { channel } },
    }),
  ).data as Channel;

// ─── 获取章节内所有句子 ID ───────────────────────────────────────────────────

/**
 * 按 book + paragraph 拉取章节内所有句子坐标
 */
export const fetchSentencesInChapter = (
  bookId: string,
  para: string,
): Promise<ISentInChapterListResponse> =>
  get<ISentInChapterListResponse>(
    `/api/v2/sentences-in-chapter?book=${bookId}&para=${para}`,
  );

// ─── 获取频道进度列表 ────────────────────────────────────────────────────────

/** 系统 channel（原文、系统逐词等）不在选版本面板里出现 */
const isSystemName = (name?: string) => /^_sys/i.test(name ?? "");

/** batch-progress 的一行 → 选版本面板用的 IChannelItem；系统 channel 返回 undefined */
export const toChannelItem = (
  item: ChannelProgress,
  index: number,
): IChannelItem | undefined => {
  if (item.is_system || isSystemName(item.name)) {
    return undefined;
  }
  const final = (item.final ?? undefined) as IFinal[] | undefined;
  let all = 0;
  let finished = 0;
  final?.forEach(([len, done]) => {
    all += len;
    if (done) finished += len;
  });
  return {
    id: index,
    uid: item.id ?? "",
    title: item.name ?? "",
    summary: item.summary ?? "",
    studio: channelOwnerStudio(item.owner) as IStudio,
    shareType: "my",
    role: channelRole(item.abilities),
    type: item.type as TChannelType,
    publicity: item.status ?? 0,
    createdAt: new Date(item.created_at ?? 0).getTime(),
    final,
    progress: all ? finished / all : 0,
    content_created_at: item.content_created_at ?? undefined,
    content_updated_at: item.content_updated_at ?? undefined,
  };
};

/**
 * 一批句子在各 channel 的完成度：这些句子上有译文且看得见的 channel，加上我能写的。
 *
 * @param sentences 句子 ID 列表，格式 `book-para-wordBegin-wordEnd`
 * @param studio    只看这个 studio（用户或团队 uuid）名下的；不传 = 全部
 */
export const fetchChannelProgress = async (
  sentences: string[],
  studio?: string,
): Promise<ChannelProgress[]> =>
  unwrap(
    await api.POST("/v3/channels/batch-progress", {
      body: { sentences, studio },
    }),
  ).data ?? [];

/* ─── channel 之间的关系设置（v3 新能力：继承自 / 捆绑逐词 / 降级 / 术语路径；服务端只记录）─── */

type ChannelLinksSingle =
  paths["/v3/channels/{channel}/links"]["get"]["responses"][200]["content"]["application/json"];
export type ChannelLinks = NonNullable<ChannelLinksSingle["data"]>;
export type ChannelRef = NonNullable<ChannelLinks["derived_from"]>;
export type ChannelLinksInput = NonNullable<
  paths["/v3/channels/{channel}/links"]["patch"]["requestBody"]
>["content"]["application/json"];

export const fetchChannelLinks = async (channel: string) =>
  unwrap(
    await api.GET("/v3/channels/{channel}/links", {
      params: { path: { channel } },
    }),
  ).data;

/** 传了哪个键就整体替换哪一种关系；单值传 null 清掉，列表传 [] 清空 */
export const updateChannelLinks = async (
  channel: string,
  body: ChannelLinksInput,
) =>
  unwrap(
    await api.PATCH("/v3/channels/{channel}/links", {
      params: { path: { channel } },
      body,
    }),
  ).data;
