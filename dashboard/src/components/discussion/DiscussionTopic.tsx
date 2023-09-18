import { useState } from "react";

import DiscussionTopicInfo from "./DiscussionTopicInfo";
import DiscussionTopicChildren from "./DiscussionTopicChildren";
import { IComment } from "./DiscussionItem";
import { TResType } from "./DiscussionListCard";

interface IWidget {
  resType?: TResType;
  topicId?: string;
  topic?: IComment;
  focus?: string;
  onItemCountChange?: Function;
  onTopicReady?: Function;
}
const DiscussionTopicWidget = ({
  resType,
  topicId,
  topic,
  focus,
  onTopicReady,
  onItemCountChange,
}: IWidget) => {
  const [count, setCount] = useState<number>();
  const [currResId, setCurrResId] = useState<string>();
  const [currTopic, setCurrTopic] = useState<IComment | undefined>(topic);
  return (
    <>
      <DiscussionTopicInfo
        topicId={topicId}
        topic={topic}
        childrenCount={count}
        onReady={(value: IComment) => {
          setCurrResId(value.resId);
          setCurrTopic(value);
          console.log("onReady", value);
          if (typeof onTopicReady !== "undefined") {
            onTopicReady(value);
          }
        }}
      />
      <DiscussionTopicChildren
        topic={currTopic}
        resId={currTopic?.resId}
        resType={resType}
        focus={focus}
        topicId={topicId}
        onItemCountChange={(count: number, e: string) => {
          //把新建回答的消息传出去。
          setCount(count);
          if (typeof onItemCountChange !== "undefined") {
            onItemCountChange(count, e);
          }
        }}
      />
    </>
  );
};

export default DiscussionTopicWidget;