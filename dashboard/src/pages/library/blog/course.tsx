import { useParams } from "react-router-dom";

import BlogNav from "../../../components/blog/BlogNav";

const Widget = () => {
  const { studio } = useParams(); //url 参数

  return <BlogNav selectedKey="course" studio={studio} />;
};

export default Widget;
