import { useNavigate, useParams, useSearchParams } from "react-router";
import type { ArticleMode } from "../../../api/article";
import ArticleEditor from "../../../features/editor/Article";

const Widget = () => {
  const { articleId, anthologyId } = useParams();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();

  const mode = searchParams.get("mode") ?? "read";
  const channelId = searchParams.get("channel");
  const anthology = searchParams.get("anthology");

  return (
    <ArticleEditor
      articleId={articleId}
      anthologyId={anthologyId}
      anthology={anthology}
      mode={mode as ArticleMode}
      channelId={channelId}
      onArticleClick={(tocAnthology, article, target) => {
        if (target === "_blank") {
          window.open(
            `${window.location.origin}${import.meta.env.BASE_URL}workspace/anthology/${tocAnthology}/${article}`,
            "_blank"
          );
        } else {
          navigate(`/workspace/anthology/${tocAnthology}/${article}`);
        }
      }}
      onAnthologySelect={(id) => {
        navigate(`/workspace/anthology/${id}/${articleId}`);
      }}
      onArticleChange={(type, id) => {
        if (anthologyId) {
          if (type === "article") {
            navigate(`/workspace/anthology/${anthologyId}/${id}`);
          } else {
            navigate(`/workspace/${type}/${id}`);
          }
        } else {
          navigate(`/workspace/${type}/${id}`);
        }
      }}
    />
  );
};

export default Widget;
