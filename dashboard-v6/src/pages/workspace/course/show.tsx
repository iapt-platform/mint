//课程详情页面
import { useMatches, useParams } from "react-router";
import { useIntl } from "react-intl";

import Course from "../../../components/course/Course";

const Widget = () => {
  const { courseId } = useParams(); //url 参数
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
      <Course id={courseId} />
    </>
  );
};

export default Widget;
