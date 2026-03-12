import {
  useLocation,
  useNavigate,
  useParams,
  useSearchParams,
} from "react-router";
import type { ArticleMode } from "../../../api/article";
import ChapterEditor from "../../../features/editor/Chapter";

const Widget = () => {
  const { id } = useParams();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { search } = useLocation();
  const mode = searchParams.get("mode") ?? "read";
  const channelId = searchParams.get("channel");

  return (
    <ChapterEditor
      chapterId={id}
      mode={mode as ArticleMode}
      channelId={channelId}
      onSelect={(id) => {
        navigate(`/workspace/tipitaka/chapter/${id}${search}`);
      }}
      onArticleChange={(type, id, target, param) => {
        const url = `workspace/tipitaka/${type}/${id}`;
        const urlSearch = param
          ? "?" + param?.map((item) => `${item.key}=${item.value}`).join("&")
          : search;
        if (target === "_blank") {
          window.open(
            `${window.location.origin}${import.meta.env.BASE_URL}${url}${urlSearch}`,
            "_blank"
          );
        } else {
          navigate(`/${url}${urlSearch}`);
        }
      }}
    />
  );
};

export default Widget;
