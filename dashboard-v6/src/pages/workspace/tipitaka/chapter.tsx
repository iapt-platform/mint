import { useNavigate, useParams, useSearchParams } from "react-router";
import type { ArticleMode } from "../../../api/article";
import ChapterEditor from "../../../features/editor/Chapter";

const Widget = () => {
  const { id } = useParams();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();

  const mode = searchParams.get("mode") ?? "read";
  const channelId = searchParams.get("channel");

  return (
    <ChapterEditor
      chapterId={id}
      mode={mode as ArticleMode}
      channelId={channelId}
      onSelect={(id) => {
        navigate(`/workspace/tipitaka/chapter/${id}`);
      }}
      onArticleChange={(type, id, target) => {
        const url = `workspace/tipitaka/${type}/${id}`;
        if (target === "_blank") {
          window.open(
            `${window.location.origin}${import.meta.env.BASE_URL}${url}`,
            "_blank"
          );
        } else {
          navigate(`/${url}`);
        }
      }}
    />
  );
};

export default Widget;
