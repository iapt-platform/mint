// src/api/nissaya.ts
import { api, unwrap } from "./client";
import type { paths } from "./schema";

/* ─── v3 缅文 nissaya 词尾卡片（取代 v2 nissaya-card）─── */

type NissayaCardResponse =
  paths["/v3/nissaya-cards/{ending}"]["get"]["responses"][200]["content"]["application/json"];
export type NissayaCard = NonNullable<NissayaCardResponse["data"]>;
export type NissayaRelation = NonNullable<NissayaCard["relations"]>[number];
export type NissayaRelationSide = NonNullable<NissayaRelation["from"]>;

/** 词尾卡片：语法术语（body 是新渲染器的节点树）+ 它表达的语法关系 */
export const fetchNissayaCard = async (ending: string, lang?: string) =>
  unwrap(
    await api.GET("/v3/nissaya-cards/{ending}", {
      params: { path: { ending }, query: { lang } },
    }),
  ).data;
