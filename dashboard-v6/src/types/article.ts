import type { ArticleMode, ArticleType } from "../api/Corpus";

export interface IArticleParam {
  type: ArticleType;
  articleId: string;
  mode?: ArticleMode;
  channelId?: string;
  book?: string;
  para?: string;
}
