import { useParams } from "react-router-dom";

import BlogNav from "../../../components/blog/BlogNav";
import CommunityChapter from "../../../components/corpus/CommunityChapter";

const Widget = () => {
  const { studio } = useParams(); //url 参数
  return (
    <>
      <BlogNav selectedKey="palicanon" studio={studio ? studio : ""} />
      <CommunityChapter studioName={studio} />
    </>
  );
};

export default Widget;
