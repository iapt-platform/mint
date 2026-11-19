// src/api/pali-word-form.ts
//
// v3 词形展开（取代 v2 case/{word}）：一个词 → 词根 → 三藏里出现过的词形与次数。
import { api, unwrap, unwrapQuiet } from "./client";
import type { paths } from "./schema";

type Body =
  paths["/v3/pali-word-forms/{word}"]["get"]["responses"][200]["content"]["application/json"];

type RawGroup = NonNullable<Body["data"]>[number];
export type WordForm = Required<NonNullable<RawGroup["forms"]>[number]>;
export interface WordFormGroup {
  base: string;
  forms: WordForm[];
}

/**
 * 第一组是输入的词本身，其后是推导出的词根；组内按出现次数降序。
 * `quiet`：失败只抛不弹提示（调用方自己显示错误时用）。
 */
export const fetchWordForms = async (
  word: string,
  { quiet = false }: { quiet?: boolean } = {},
): Promise<WordFormGroup[]> =>
  (
    (quiet ? unwrapQuiet : unwrap)(
      await api.GET("/v3/pali-word-forms/{word}", {
        params: { path: { word } },
      }),
    ).data ?? []
  ).map((g) => ({
    base: g.base ?? "",
    forms: (g.forms ?? []).map((f) => ({
      word: f.word ?? "",
      count: f.count ?? 0,
      bold: f.bold ?? 0,
    })),
  }));

type SuggestionBody =
  paths["/v3/pali-word-suggestions"]["get"]["responses"][200]["content"]["application/json"];
export type WordSuggestion = Required<
  NonNullable<SuggestionBody["data"]>[number]
>;

/**
 * 巴利词补全（取代 v2 pali-word-index?view=key）：前缀 → 三藏里出现过的词形。
 * 与输入相同的在前，其余按出现次数降序；不打变音符号也能补全。失败只抛不弹（补全是辅助功能）。
 */
export const fetchWordSuggestions = async (
  q: string,
  signal?: AbortSignal,
): Promise<WordSuggestion[]> =>
  (
    unwrapQuiet(
      await api.GET("/v3/pali-word-suggestions", {
        params: { query: { q } },
        signal,
      }),
    ).data ?? []
  ).map((w) => ({
    word: w.word ?? "",
    count: w.count ?? 0,
    bold: w.bold ?? 0,
  }));
