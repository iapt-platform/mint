import { ArticleMode, ArticleType } from "./Article";
import AnthologyDetail from "./AnthologyDetail";
import "./article.css";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  onArticleChange?: Function;
  onFinal?: Function;
  onLoad?: Function;
  onLoading?: Function;
  onError?: Function;
}
const TypeAnthologyWidget = ({
  type,
  channelId,
  articleId,
  mode = "read",
  onArticleChange,
  onLoading,
  onError,
}: IWidget) => {
  const channels = channelId?.split("_");
  return (
    <AnthologyDetail
      onArticleSelect={(anthologyId: string, keys: string[]) => {
        if (typeof onArticleChange !== "undefined" && keys.length > 0) {
          onArticleChange("article", keys[0], { anthologyId: anthologyId });
        }
      }}
      onLoading={(loading: boolean) => {
        if (typeof onLoading !== "undefined") {
          onLoading(loading);
        }
      }}
      onError={(code: number, message: string) => {
        if (typeof onError !== "undefined") {
          onError(code, message);
        }
      }}
      channels={channels}
      aid={articleId}
    />
  );
};

export default TypeAnthologyWidget;
