import type { ArticleMode } from "../../api/Article";

import "./article.css";
import ArticleLayout from "./components/ArticleLayout";
import { useTerm } from "./hooks/useTerm";

interface IWidget {
  id?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
}

const TypeTermWidget = ({ channelId, id, mode = "read" }: IWidget) => {
  const { articleData, articleHtml, errorCode, loading } = useTerm({
    id,
    channelId,
    mode,
  });

  const channels = channelId?.split("_");

  return (
    <div>
      <ArticleLayout
        title={articleData?.title}
        subTitle={articleData?.subtitle}
        content={articleData?.content ?? ""}
        html={articleHtml}
        path={articleData?.path}
        editor={articleData?.editor}
        created_at={articleData?.created_at}
        updated_at={articleData?.updated_at}
        channels={channels}
        loading={loading}
        errorCode={errorCode}
      />
    </div>
  );
};

export default TypeTermWidget;
