// 全文检索的 category 分面是书的标签，藏别、层级、书系混在一个列表里。
// 这里把它分成三组显示；过滤时仍然都放进 category 参数（后端要求全部命中）。
import type { ChapterLayer } from "../../api/tipitaka-search";

export interface ICategoryOption {
  /** 索引里的标签值 */
  key: string;
  /** 界面文案的 id */
  labelId: string;
}

/** 层级：一段文字只属于其中一层，组内单选 */
export const LAYER_OPTIONS: ICategoryOption[] = [
  { key: "mūla", labelId: "search.layer.mula" },
  { key: "aṭṭhakathā", labelId: "search.layer.atthakatha" },
  { key: "ṭīkā", labelId: "search.layer.tika" },
  { key: "añña", labelId: "search.layer.anna" },
];

/** 藏别：组内单选 */
export const PITAKA_OPTIONS: ICategoryOption[] = [
  { key: "vinaya", labelId: "search.pitaka.vinaya" },
  { key: "sutta", labelId: "search.pitaka.sutta" },
  { key: "abhidhamma", labelId: "search.pitaka.abhidhamma" },
];

const LAYER_KEYS = new Set(LAYER_OPTIONS.map((o) => o.key));
const PITAKA_KEYS = new Set(PITAKA_OPTIONS.map((o) => o.key));

/** 不属于层级、藏别的其余标签（尼柯耶、书系等），组内可多选（全部命中） */
export const isOtherCategory = (key: string): boolean =>
  !LAYER_KEYS.has(key) && !PITAKA_KEYS.has(key);

export const isLayerCategory = (key: string): boolean => LAYER_KEYS.has(key);
export const isPitakaCategory = (key: string): boolean => PITAKA_KEYS.has(key);

/** 段落的 category → 章节接口的 layer 值（用于统一的层级标签） */
const CATEGORY_TO_LAYER: Record<string, ChapterLayer> = {
  mūla: "mula",
  aṭṭhakathā: "atthakatha",
  ṭīkā: "tika",
  añña: "anna",
};

export const layerOf = (category?: string[]): ChapterLayer | undefined => {
  for (const c of category ?? []) {
    if (CATEGORY_TO_LAYER[c]) return CATEGORY_TO_LAYER[c];
  }
  return undefined;
};
