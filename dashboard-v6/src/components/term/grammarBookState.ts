/** 语法手册可重入的状态：检索词、选中的术语（按 id 或按词） */
export interface IGrammarBookState {
  q?: string;
  id?: string;
  word?: string;
}

/** 状态 → 地址栏查询串（/grammar-book?q=&id=&word=），空状态给空串 */
export function grammarBookSearch(state: IGrammarBookState): string {
  const params = new URLSearchParams();
  if (state.q) params.set("q", state.q);
  if (state.id) params.set("id", state.id);
  if (state.word) params.set("word", state.word);
  const search = params.toString();
  return search ? `?${search}` : "";
}

/** 地址栏查询串 → 状态 */
export function parseGrammarBookSearch(
  params: URLSearchParams,
): IGrammarBookState {
  return {
    q: params.get("q") ?? undefined,
    id: params.get("id") ?? undefined,
    word: params.get("word") ?? undefined,
  };
}
