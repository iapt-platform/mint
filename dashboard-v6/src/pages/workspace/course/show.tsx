//课程详情页面
import { useParams } from "react-router";

import Course from "../../../components/course/Course";

const Widget = () => {
  const { courseId } = useParams(); //url 参数

  return <Course id={courseId} />;
};

export default Widget;
