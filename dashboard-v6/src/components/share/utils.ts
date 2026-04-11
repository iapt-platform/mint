/**
 * - 1 PCS 文档
- 2 Channel 版本
- 3 Article 文章
- 4 Collection 文集
- 5 版本片段
 */
export const EResType = {
  pcs: 1,
  channel: 2,
  article: 3,
  collection: 4,
  workflow: 6,
  project: 7,
  modal: 8,
} as const;

export type EResType = (typeof EResType)[keyof typeof EResType];
