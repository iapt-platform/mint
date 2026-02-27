import type { ArticleMode, ArticleType } from "../api/Article";

export const SENTENCE_FIX_WIDTH = 800;

export interface IArticleParam {
  type: ArticleType;
  articleId: string;
  mode?: ArticleMode;
  channelId?: string;
  book?: string;
  para?: string;
}
