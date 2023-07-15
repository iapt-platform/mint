import { Divider } from "antd";

import CommentTopicInfo from "./DiscussionTopicInfo";
import CommentTopicChildren from "./DiscussionTopicChildren";
import { IComment } from "./DiscussionItem";

interface IWidget {
  topicId?: string;
  onItemCountChange?: Function;
  onTopicReady?: Function;
}
const DiscussionTopicWidget = ({
  topicId,
  onTopicReady,
  onItemCountChange,
}: IWidget) => {
  return (
    <>
      <CommentTopicInfo
        topicId={topicId}
        onReady={(value: IComment) => {
          console.log("on Topic Ready", value);
          if (typeof onTopicReady !== "undefined") {
            onTopicReady(value);
          }
        }}
      />
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
    </>
  );
};

export default DiscussionTopicWidget;
