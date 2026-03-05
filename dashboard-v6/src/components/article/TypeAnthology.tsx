import "./article.css";

import type { ArticleMode } from "../../api/Article";
import AnthologyDetail from "../anthology/AnthologyReader";
import type { TTarget } from "../../types";

interface IWidget {
  id?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  onArticleChange?: (
    type: string,
    articleId: string,
    target?: TTarget,
    extra?: { anthologyId?: string }
  ) => void;
}

const TypeAnthologyWidget = ({ channelId, id, onArticleChange }: IWidget) => {
  const channels = channelId ? channelId.split("_") : undefined;

  return (
    <AnthologyDetail
      channels={channels}
      id={id}
      onArticleClick={(anthologyId, articleId, target) => {
        onArticleChange?.("article", articleId, target, {
          anthologyId,
        });
      }}
    />
  );
};

export default TypeAnthologyWidget;
