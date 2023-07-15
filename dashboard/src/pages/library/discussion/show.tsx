import { useParams } from "react-router-dom";

import CommentTopic from "../../../components/discussion/CommentTopic";

const Widget = () => {
  // TODO
  const { id } = useParams(); //url 参数

  return (
    <div>
      <div>锚点</div>
      <div>
        <CommentTopic topicId={id} />
      </div>
    </div>
  );
};

export default Widget;
