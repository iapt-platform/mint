import { useParams } from "react-router-dom";
import CommentAnchor from "../../../components/comment/CommentAnchor";

import CommentTopic from "../../../components/comment/CommentTopic";

const Widget = () => {
  // TODO
  const { id } = useParams(); //url 参数

  return (
    <div>
      <div>
        <CommentAnchor id={id} />
      </div>
      <div>
        <CommentTopic topicId={id} />
      </div>
    </div>
  );
};

export default Widget;
