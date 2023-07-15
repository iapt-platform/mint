import { Divider } from "antd";

import CommentTopicInfo from "./CommentTopicInfo";
import CommentTopicChildren from "./CommentTopicChildren";

interface IWidget {
  topicId?: string;
  onItemCountChange?: Function;
}
const CommentTopicWidget = ({ topicId, onItemCountChange }: IWidget) => {
  return (
    <div>
      <CommentTopicInfo topicId={topicId} />
      <Divider />
      <CommentTopicChildren
        topicId={topicId}
        onItemCountChange={(count: number, e: string) => {
          //把新建回答的消息传出去。
          if (typeof onItemCountChange !== "undefined") {
            onItemCountChange(count, e);
          }
        }}
      />
    </div>
  );
};

export default CommentTopicWidget;
