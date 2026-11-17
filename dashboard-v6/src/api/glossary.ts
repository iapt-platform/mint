// src/api/glossary.ts
import { api, unwrapQuiet } from "./client";
import type { paths } from "./schema";
import type { ITerm } from "../reducers/term-vocabulary";

/* ─── v3 术语词汇表（取代 v2 term-vocabulary）：语法术语表 / 社区译名表，整表拉取后本地查 ─── */

type GlossaryList =
  paths["/v3/glossaries/{kind}"]["get"]["responses"][200]["content"]["application/json"];
export type GlossaryEntry = NonNullable<GlossaryList["data"]>[number];
export type GlossaryKind =
  paths["/v3/glossaries/{kind}"]["get"]["parameters"]["path"]["kind"];

/**
 * 取一张词汇表，转成 store（reducers/term-vocabulary）里的条目形状。
 * 启动时调用，失败不弹提示（unwrapQuiet），由调用方决定怎么处理。
 */
export const fetchGlossary = async (
  kind: GlossaryKind,
  lang?: string,
): Promise<ITerm[]> => {
  const json = unwrapQuiet(
    await api.GET("/v3/glossaries/{kind}", {
      params: { path: { kind }, query: { lang } },
    }),
  );
  return (json.data ?? []).map((item: GlossaryEntry) => ({
    guid: item.id,
    word: item.word ?? "",
    tag: item.tag ?? undefined,
    meaning: item.meaning ?? "",
    other_meaning: item.other_meaning ?? undefined,
  }));
};
