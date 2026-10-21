// src/api/discussion.ts
//
// 评论线程（话题 + 一层回复），走 v3：/v3/discussions。
// 请求与响应的类型全部取自 schema.d.ts（由 OpenAPI 生成），不要手写字段。

import type { IUser } from "./Auth";
import { api, unwrap, unwrapQuiet } from "./client";
import { ApiError } from "./error";
import type { paths } from "./schema";

/**
 * 通用的资源类型：transfer、webhook、右侧面板等也在用，比讨论能挂的对象多。
 * 讨论能挂的对象见 {@link TDiscussionTarget}。
 */
export type TResType =
  | "article"
  | "channel"
  | "chapter"
  | "sentence"
  | "wbw"
  | "term"
  | "task";

type TopicQuery = paths["/v3/discussions"]["get"]["parameters"]["query"];
/** 按经文范围列话题的过滤条件（channel 在路径上，单独传） */
export type TTextTopicQuery = NonNullable<
  paths["/v3/tipitaka-discussions/{channel}"]["get"]["parameters"]["query"]
>;
type SummaryQuery =
  paths["/v3/discussions/summary"]["get"]["parameters"]["query"];
type TopicBody = NonNullable<
  paths["/v3/discussions"]["post"]["requestBody"]
>["content"]["application/json"];
type PatchBody = NonNullable<
  paths["/v3/discussions/{discussion}"]["patch"]["requestBody"]
>["content"]["application/json"];
type DiscussionData = NonNullable<
  paths["/v3/discussions/{discussion}"]["get"]["responses"][200]["content"]["application/json"]["data"]
>;
type BatchCountBody = NonNullable<
  paths["/v3/discussions/batch-count"]["post"]["requestBody"]
>["content"]["application/json"];
/** 讨论角标的对象：句子或逐词 */
export type TDiscussionBadgeTarget = BatchCountBody["targets"][number];
/** 一个对象的讨论角标：未关闭的评论线程数与其中我发起的 */
export type TDiscussionBadge = NonNullable<
  paths["/v3/discussions/batch-count"]["post"]["responses"][200]["content"]["application/json"]["data"]
>[number];
export type TDiscussionSummary = NonNullable<
  paths["/v3/discussions/summary"]["get"]["responses"][200]["content"]["application/json"]["data"]
>;

/** 讨论能挂的对象类型 */
export type TDiscussionTarget = TopicQuery["target_type"];
/** 类型：评论线程（THREAD_TYPES）+ 批注（note 边注、commentary 义注 / 复注对应） */
export type TDiscussionType = NonNullable<TopicQuery["type"]>;

/**
 * 评论线程的类型（与后端 Discussion::THREAD_TYPES 一致）。讨论面板只处理这几种；
 * 批注注入阅读页、没有回复与开关，不在讨论面板里新建。
 */
export const THREAD_TYPES: readonly TDiscussionType[] = [
  "discussion",
  "qa",
  "help",
  "issue",
  "glossary",
];
/** 严重程度（几种话题类型的并集，按类型拆分见 SEVERITY_OPTIONS） */
export type TSeverity = NonNullable<TopicBody["severity"]>;
/** 话题状态 */
export type TDiscussionStatus = NonNullable<TopicQuery["status"]>;

/**
 * 各话题类型可选的 severity（与后端 Discussion::SEVERITIES 一致，不在表里的类型不收）。
 * 规格里 severity 是几种类型的并集，按类型拆分只能写在这里。
 */
export const SEVERITY_OPTIONS: Partial<Record<TDiscussionType, TSeverity[]>> = {
  issue: ["error", "warning", "suggestion"],
  help: ["urgent", "normal"],
};

/** 当前用户对一条记录能做的动作，由后端按权限算好 */
export interface IDiscussionAbilities {
  update: boolean;
  delete: boolean;
  close: boolean;
  reply: boolean;
}

export const NO_ABILITIES: IDiscussionAbilities = {
  update: false,
  delete: false,
  close: false,
  reply: false,
};

const DISCUSSION_TARGETS: readonly TDiscussionTarget[] = [
  "sentence",
  "wbw",
  "article",
  "term",
  "task",
];

/** 这个资源类型能不能挂讨论（chapter、channel 不能） */
export const asDiscussionTarget = (
  resType?: TResType
): TDiscussionTarget | undefined =>
  DISCUSSION_TARGETS.find((value) => value === resType);

/** 某个话题的回复数变化：新建回复后从话题视图传回列表，更新列表上的计数 */
export interface IAnswerCount {
  id: string;
  count: number;
}

/** 界面用的一条评论：话题（parent 为空）或回复 */
export interface IComment {
  id?: string; //id未提供为尚未创建的模板话题
  resId?: string;
  resType?: TDiscussionTarget;
  type: TDiscussionType;
  severity?: string | null;
  tplId?: string | null;
  user: IUser;
  /** AI 凭人类授权代写时的模型；此时 user 是授权人 */
  coEditor?: IUser;
  parent?: string | null;
  title?: string | null;
  content?: string | null;
  html?: string;
  summary?: string;
  status?: TDiscussionStatus;
  childrenCount?: number;
  abilities: IDiscussionAbilities;
  /** 删除了但还挂着回复的话题（墓碑）：内容为空，回复照常显示 */
  deleted?: boolean;
  newTpl?: boolean;
  /** 所在句子的坐标：只有按经文范围列出时有（`/v3/tipitaka-discussions`） */
  sentence?: {
    book: number;
    paragraph: number;
    wordStart: number;
    wordEnd: number;
  };
  createdAt?: string;
  updatedAt?: string;
}

/** 响应里的操作者摘要（editor / co_editor）→ 界面用的 IUser */
const toUser = (actor: DiscussionData["editor"]): IUser => ({
  id: actor?.id ?? "",
  nickName: actor?.nickName ?? "",
  userName: actor?.userName ?? "",
  avatar: actor?.avatar,
  roles: actor?.roles,
});

export const toIComment = (value: DiscussionData): IComment => ({
  id: value.id,
  resId: value.target_id,
  resType: asDiscussionTarget(value.target_type as TResType),
  type: value.type as TDiscussionType,
  severity: value.severity,
  tplId: value.tpl_id,
  user: toUser(value.editor),
  coEditor: value.co_editor ? toUser(value.co_editor) : undefined,
  parent: value.parent_id,
  title: value.title,
  content: value.content,
  html: value.html,
  summary: value.summary,
  status: value.status as TDiscussionStatus,
  childrenCount: value.reply_count,
  abilities: { ...NO_ABILITIES, ...value.abilities },
  deleted: !!value.deleted_at,
  sentence: value.sentence
    ? {
        book: value.sentence.book ?? 0,
        paragraph: value.sentence.paragraph ?? 0,
        wordStart: value.sentence.word_start ?? 0,
        wordEnd: value.sentence.word_end ?? 0,
      }
    : undefined,
  createdAt: value.created_at,
  updatedAt: value.updated_at,
});

/** 单个资源的响应里 data 必然存在；缺了说明后端契约坏了，当作失败处理 */
const one = (body: { data?: DiscussionData }): IComment => {
  if (!body.data) {
    throw new ApiError(500, "empty discussion response");
  }
  return toIComment(body.data);
};

/** 某个对象上的话题列表 */
export const fetchTopics = async (
  query: TopicQuery
): Promise<{ items: IComment[]; total: number }> => {
  const body = unwrap(await api.GET("/v3/discussions", { params: { query } }));
  return {
    items: (body.data ?? []).map(toIComment),
    total: body.meta?.total ?? 0,
  };
};

/**
 * 一段经文（整章 / 段落区间 / 整本书）里挂在句子上的话题，按经文顺序，每条带 sentence 坐标。
 * channel 必填：同一段在不同译文里是不同的句子。
 */
export const fetchTextTopics = async (
  channel: string,
  query: TTextTopicQuery
): Promise<{ items: IComment[]; total: number }> => {
  const body = unwrap(
    await api.GET("/v3/tipitaka-discussions/{channel}", {
      params: { path: { channel }, query },
    })
  );
  return {
    items: (body.data ?? []).map(toIComment),
    total: body.meta?.total ?? 0,
  };
};

/** 某个对象上的话题概况：各状态的数量、当前用户能发起的类型 */
export const fetchTopicSummary = async (
  query: SummaryQuery
): Promise<TDiscussionSummary> =>
  unwrap(await api.GET("/v3/discussions/summary", { params: { query } }))
    .data ?? {};

export const fetchDiscussion = async (id: string): Promise<IComment> =>
  one(
    unwrap(
      await api.GET("/v3/discussions/{discussion}", {
        params: { path: { discussion: id } },
      })
    )
  );

/** 话题的回复，按时间正序 */
export const fetchReplies = async (
  topicId: string,
  perPage = 200
): Promise<IComment[]> =>
  (
    unwrap(
      await api.GET("/v3/discussions/{discussion}/replies", {
        params: { path: { discussion: topicId }, query: { per_page: perPage } },
      })
    ).data ?? []
  ).map(toIComment);

export const createTopic = async (body: TopicBody): Promise<IComment> =>
  one(unwrap(await api.POST("/v3/discussions", { body })));

export const createReply = async (
  topicId: string,
  content: string
): Promise<IComment> =>
  one(
    unwrap(
      await api.POST("/v3/discussions/{discussion}/replies", {
        params: { path: { discussion: topicId } },
        body: { content },
      })
    )
  );

/** 局部更新：只发要改的字段 */
export const updateDiscussion = async (
  id: string,
  body: PatchBody
): Promise<IComment> =>
  one(
    unwrap(
      await api.PATCH("/v3/discussions/{discussion}", {
        params: { path: { discussion: id } },
        body,
      })
    )
  );

/** 软删除，返回删除后的资源 */
export const deleteDiscussion = async (id: string): Promise<IComment> =>
  one(
    unwrap(
      await api.DELETE("/v3/discussions/{discussion}", {
        params: { path: { discussion: id } },
      })
    )
  );

/** 一批对象的讨论角标，按请求顺序返回（没有讨论的为 0）。一次最多 500 个，调用方自己分批 */
export const fetchDiscussionBadges = async (
  targets: TDiscussionBadgeTarget[]
): Promise<TDiscussionBadge[]> =>
  unwrapQuiet(
    await api.POST("/v3/discussions/batch-count", { body: { targets } })
  ).data ?? [];
