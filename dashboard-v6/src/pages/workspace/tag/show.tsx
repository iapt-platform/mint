import { useParams } from "react-router";

import TagShow from "../../../components/tag/TagShow";

const Widget = () => {
  const { tagId } = useParams(); //url 参数

  return <TagShow tagId={tagId} />;
};

export default Widget;
