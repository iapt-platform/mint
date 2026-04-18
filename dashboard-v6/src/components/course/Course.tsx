// /src/pages/course/CourseDetailPage.tsx（或原路径的 Widget.tsx）

import { Divider } from "antd";
import { useCourse } from "./hooks/useCourse";
import ArticleSkeleton from "../article/components/ArticleSkeleton";
import ErrorResult from "../general/ErrorResult";
import CourseHead from "./CourseHead";
import CourseIntro from "./CourseIntro";
import TextBook from "./TextBook";

interface IWidget {
  id?: string;
}

const Course = ({ id }: IWidget) => {
  const { data, loading, errorCode } = useCourse(id);

  if (loading) return <ArticleSkeleton />;
  if (errorCode) return <ErrorResult code={errorCode} />;

  return (
    <div>
      <CourseHead data={data ?? undefined} />
      <Divider />
      <CourseIntro intro={data?.content} />
      <Divider />
      <TextBook anthologyId={data?.anthology_id} courseId={data?.id} />
    </div>
  );
};

export default Course;
