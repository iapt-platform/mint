import {
  useLocation,
  useMatches,
  useNavigate,
  useParams,
  useSearchParams,
} from "react-router";
import { useIntl } from "react-intl";
import type { ArticleMode } from "../../../api/article";
import CsParaEditor from "../../../features/editor/CsPara";

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
  const prefix = intl.formatMessage({ id: "pages.tipitaka.cs-para.title" });

  const mode = searchParams.get("mode") ?? "read";
  const channelId = searchParams.get("channel");

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
      <CsParaEditor
        articleId={id}
        mode={mode as ArticleMode}
        channelId={channelId}
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
