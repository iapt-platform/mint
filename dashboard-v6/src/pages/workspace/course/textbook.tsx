//课程详情页面
import { useParams } from "react-router";

import TextBookEditor from "../../../features/editor/TextBook";

const Widget = () => {
  const { courseId, articleId } = useParams(); //url 参数

  return <TextBookEditor courseId={courseId} articleId={articleId} />;
};

export default Widget;
