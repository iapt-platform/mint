import {
  useLocation,
  useNavigate,
  useParams,
  useSearchParams,
} from "react-router";
import type { ArticleMode } from "../../../api/article";
import ParaEditor from "../../../features/editor/Paragraph";

const Widget = () => {
  const { id } = useParams();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { search } = useLocation();
  const mode = searchParams.get("mode") ?? "read";
  const channelId = searchParams.get("channel");

  return (
    <ParaEditor
      chapterId={id}
      mode={mode as ArticleMode}
      channelId={channelId}
      onSelect={(id) => {
        navigate(`/workspace/tipitaka/para/${id}${search}`);
      }}
      onArticleChange={(type, id, target, param) => {
        const url = `workspace/tipitaka/${type}/${id}`;
        const urlSearch =
          param && param.length > 0
            ? "?" +
              param.map((item) => `${item.key}=${item.value}`).join("&")
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
