//课程详情页面
import {
  useLocation,
  useMatches,
  useNavigate,
  useParams,
  useSearchParams,
} from "react-router";
import { useIntl } from "react-intl";

import type { ArticleMode } from "../../../api/article";
import TextBookEditor from "../../../features/editor/TextBook";

const Widget = () => {
  const { courseId, articleId } = useParams(); //url 参数
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { search } = useLocation();
  const intl = useIntl();
  const matches = useMatches() as {
    data?: { title?: string; name?: string; word?: string };
  }[];
  const data = [...matches].reverse().find((m) => m.data)?.data;
  const name = data?.title ?? data?.name ?? data?.word;
  const prefix = intl.formatMessage({ id: "columns.studio.course.title" });

  const mode = (searchParams.get("mode") ?? "read") as ArticleMode;
  const channelId = searchParams.get("channel");

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
      <TextBookEditor
        courseId={courseId}
        articleId={articleId}
        mode={mode}
        channelId={channelId}
        onArticleClick={(_anthologyId, id, target) => {
          const url = `workspace/course/${courseId}/textbook/${id}`;
          if (target === "_blank") {
            window.open(
              `${window.location.origin}${import.meta.env.BASE_URL}${url}${search}`,
              "_blank"
            );
          } else {
            navigate(`/${url}${search}`);
          }
        }}
        onArticleChange={(type, id, target, param) => {
          const url =
            type === "textbook"
              ? `workspace/course/${courseId}/textbook/${id}`
              : `workspace/tipitaka/${type}/${id}`;
          const urlSearch =
            param && param.length > 0
              ? "?" + param.map((item) => `${item.key}=${item.value}`).join("&")
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
