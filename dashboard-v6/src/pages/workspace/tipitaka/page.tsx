import { useEffect } from "react";
import {
  useLocation,
  useMatches,
  useNavigate,
  useParams,
  useSearchParams,
} from "react-router";
import { useIntl } from "react-intl";
import type { ArticleMode } from "../../../api/article";
import TypePage from "../../../components/article/TypePage";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";
import { useSaveRecent } from "../../../hooks/useSaveRecent";

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
  const prefix = intl.formatMessage({ id: "pages.tipitaka.page.title" });

  const mode = searchParams.get("mode") ?? "read";
  const channelId = searchParams.get("channel");

  const currUser = useAppSelector(currentUser);
  const { save } = useSaveRecent();

  // 记录最近访问
  useEffect(() => {
    if (!currUser?.id || !id) return;
    const paramObj = search
      ? Object.fromEntries(new URLSearchParams(search))
      : undefined;
    save({
      type: "page",
      article_id: id,
      param: JSON.stringify(paramObj),
    });
  }, [currUser?.id, id, search, save]);

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
      <TypePage
        articleId={id}
        mode={mode as ArticleMode}
        channelId={channelId}
        onArticleChange={(type, articleId, target) => {
          const url = `workspace/tipitaka/${type}/${articleId}`;
          if (target === "_blank") {
            window.open(
              `${window.location.origin}${import.meta.env.BASE_URL}${url}${search}`,
              "_blank"
            );
          } else {
            navigate(`/${url}${search}`);
          }
        }}
      />
    </>
  );
};

export default Widget;
