import { useParams } from "react-router-dom";

import AnthologyList from "../../../components/article/AnthologyList";
import BlogNav from "../../../components/blog/BlogNav";

const Widget = () => {
  const { studio } = useParams(); //url 参数

  return (
    <>
      <BlogNav selectedKey="anthology" studio={studio ? studio : ""} />
      <AnthologyList studioName={studio} />
    </>
  );
};

export default Widget;
