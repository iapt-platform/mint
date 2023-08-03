import { Divider } from "antd";

import DiscussionTopicInfo from "./DiscussionTopicInfo";
import DiscussionTopicChildren from "./DiscussionTopicChildren";
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
      <DiscussionTopicInfo
        topicId={topicId}
        onReady={(value: IComment) => {
          console.log("on Topic Ready", value);
          if (typeof onTopicReady !== "undefined") {
            onTopicReady(value);
          }
        }}
      />
      <Divider />
      <DiscussionTopicChildren
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
