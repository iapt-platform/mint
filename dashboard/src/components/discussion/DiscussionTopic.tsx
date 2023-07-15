import { Divider } from "antd";

import CommentTopicInfo from "./DiscussionTopicInfo";
import CommentTopicChildren from "./DiscussionTopicChildren";

interface IWidget {
  topicId?: string;
  onItemCountChange?: Function;
}
const DiscussionTopicWidget = ({ topicId, onItemCountChange }: IWidget) => {
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

export default DiscussionTopicWidget;
