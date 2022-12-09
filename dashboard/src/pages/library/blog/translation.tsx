import { useParams } from "react-router-dom";
import BlogNav from "../../../components/blog/BlogNav";

const Widget = () => {
  // TODO
  const { studio } = useParams(); //url 参数

  return (
    <>
      <BlogNav selectedKey="palicanon" studio={studio ? studio : ""} />
    </>
  );
};

export default Widget;
