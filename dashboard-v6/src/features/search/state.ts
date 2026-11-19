// 搜索面板的状态：弹窗里存在组件 state，整页（/search）存在地址栏，
// 两边共用同一套字段，「在整页打开」只是把它序列化成查询串。
import type {
  BoldScope,
  ChapterLayer,
  SearchMode,
} from "../../api/tipitaka-search";

export type SearchTab = "fulltext" | "definition" | "title" | "page";

export const SEARCH_TABS: SearchTab[] = [
  "fulltext",
  "definition",
  "title",
  "page",
];

export const SEARCH_MODES: SearchMode[] = ["fuzzy", "exact", "definition"];

const CHAPTER_LAYERS: ChapterLayer[] = ["mula", "atthakatha", "tika", "anna"];

/** 定义标签页的黑体范围：all 是不限 */
export type DefinitionBold = BoldScope | "all";
export const DEFINITION_BOLDS: DefinitionBold[] = ["only", "all", "exclude"];

export interface ISearchState {
  q: string;
  tab: SearchTab;
  /** 全文：匹配方式 */
  mode: SearchMode;
  /** 全文：已选的分类（书的标签），全部命中 */
  category: string[];
  /** 全文：页码 */
  page: number;
  /** 标题：层级 */
  layer?: ChapterLayer;
  /** 标题：页码 */
  titlePage: number;
  /** 定义：勾选的词形；undefined 表示按默认（输入词那一组的全部词形） */
  forms?: string[];
  /** 定义：黑体范围，默认只看黑体（被解释的词） */
  bold: DefinitionBold;
  /** 定义：目录树选中的节点（节点 key = 它的 tag 用逗号连起来）；只看属于这些节点的书 */
  scope: string[];
  /** 定义：在目录范围内再勾选的书（book_id） */
  bookIds: number[];
  /** 定义：页码 */
  defPage: number;
  /** 页码：当前看的版本（PTS / My / VRI / Thai / Other / CS）；不给就取第一个有结果的 */
  edition?: string;
  /** 页码：目录树选中的节点（同定义的 scope，两个标签页各管各的） */
  pageScope: string[];
}

export const initialSearchState: ISearchState = {
  q: "",
  tab: "fulltext",
  mode: "fuzzy",
  category: [],
  page: 1,
  titlePage: 1,
  bold: "only",
  scope: [],
  bookIds: [],
  defPage: 1,
  pageScope: [],
};

/** 纯数字或「卷.页」：大概率是在找页码 */
export const looksLikePageNumber = (q: string): boolean =>
  /^\d+(\.\d+)?$/.test(q.trim());

/** 「100」→ 第 100 页；「2.100」→ 第 2 卷第 100 页 */
export const parsePageQuery = (
  q: string,
): { page: number; volume?: number } | null => {
  const m = /^(?:(\d+)\.)?(\d+)$/.exec(q.trim());
  if (!m) return null;
  const page = Number(m[2]);
  const volume = m[1] ? Number(m[1]) : undefined;
  if (page < 1 || (volume !== undefined && volume < 1)) return null;
  return { page, volume };
};

const oneOf = <T extends string>(
  list: readonly T[],
  v: string | null,
): T | undefined =>
  v && (list as readonly string[]).includes(v) ? (v as T) : undefined;

const positive = (v: string | null): number => {
  const n = Number(v);
  return Number.isInteger(n) && n > 0 ? n : 1;
};

const splitList = (v: string | null): string[] =>
  (v ?? "")
    .split(",")
    .map((s) => s.trim())
    .filter(Boolean);

export const parseSearchParams = (sp: URLSearchParams): ISearchState => ({
  q: sp.get("q") ?? "",
  tab: oneOf(SEARCH_TABS, sp.get("tab")) ?? "fulltext",
  mode: oneOf(SEARCH_MODES, sp.get("mode")) ?? "fuzzy",
  category: splitList(sp.get("category")),
  page: positive(sp.get("page")),
  layer: oneOf(CHAPTER_LAYERS, sp.get("layer")),
  titlePage: positive(sp.get("title_page")),
  forms: sp.has("forms") ? splitList(sp.get("forms")) : undefined,
  bold: oneOf(DEFINITION_BOLDS, sp.get("bold")) ?? "only",
  scope: (sp.get("scope") ?? "").split(";").filter(Boolean),
  bookIds: splitList(sp.get("book_id"))
    .map(Number)
    .filter((n) => Number.isInteger(n) && n > 0),
  defPage: positive(sp.get("def_page")),
  edition: sp.get("edition") ?? undefined,
  pageScope: (sp.get("page_scope") ?? "").split(";").filter(Boolean),
});

/** 只写非默认值，地址短一些 */
export const toSearchParams = (s: ISearchState): URLSearchParams => {
  const sp = new URLSearchParams();
  if (s.q) sp.set("q", s.q);
  if (s.tab !== "fulltext") sp.set("tab", s.tab);
  if (s.mode !== "fuzzy") sp.set("mode", s.mode);
  if (s.category.length) sp.set("category", s.category.join(","));
  if (s.page > 1) sp.set("page", String(s.page));
  if (s.layer) sp.set("layer", s.layer);
  if (s.titlePage > 1) sp.set("title_page", String(s.titlePage));
  if (s.forms) sp.set("forms", s.forms.join(","));
  if (s.bold !== "only") sp.set("bold", s.bold);
  if (s.scope.length) sp.set("scope", s.scope.join(";"));
  if (s.bookIds.length) sp.set("book_id", s.bookIds.join(","));
  if (s.defPage > 1) sp.set("def_page", String(s.defPage));
  if (s.edition) sp.set("edition", s.edition);
  if (s.pageScope.length) sp.set("page_scope", s.pageScope.join(";"));
  return sp;
};
