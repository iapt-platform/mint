//课程详情页面
import { useMatches, useParams } from "react-router";
import { useIntl } from "react-intl";

import TextBookEditor from "../../../features/editor/TextBook";

const Widget = () => {
  const { courseId, articleId } = useParams(); //url 参数
  const intl = useIntl();
  const matches = useMatches() as {
    data?: { title?: string; name?: string; word?: string };
  }[];
  const data = [...matches].reverse().find((m) => m.data)?.data;
  const name = data?.title ?? data?.name ?? data?.word;
  const prefix = intl.formatMessage({ id: "columns.studio.course.title" });

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
      <TextBookEditor courseId={courseId} articleId={articleId} />
    </>
  );
};

export default Widget;
