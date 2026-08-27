import {
  useLocation,
  useMatches,
  useNavigate,
  useParams,
  useSearchParams,
} from "react-router";
import { useIntl } from "react-intl";
import type { ArticleMode } from "../../../api/article";
import ParaEditor from "../../../features/editor/Paragraph";

const Widget = () => {
  const { id } = useParams();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { search } = useLocation();
  const intl = useIntl();
  const matches = useMatches() as {
    data?: { title?: string; name?: string; word?: string };
  }[];
  const data = [...matches].reverse().find((m) => m.data)?.data;
  const name = data?.title ?? data?.name ?? data?.word;
  const prefix = intl.formatMessage({ id: "pages.tipitaka.para.title" });

  const mode = searchParams.get("mode") ?? "read";
  const channelId = searchParams.get("channel");

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
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
    </>
  );
};

export default Widget;
