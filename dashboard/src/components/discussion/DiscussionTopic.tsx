import { useState } from "react";

import DiscussionTopicInfo from "./DiscussionTopicInfo";
import DiscussionTopicChildren from "./DiscussionTopicChildren";
import { IComment } from "./DiscussionItem";
import { TResType } from "./DiscussionListCard";

interface IWidget {
  resType?: TResType;
  topicId?: string;
  focus?: string;
  onItemCountChange?: Function;
  onTopicReady?: Function;
}
const DiscussionTopicWidget = ({
  resType,
  topicId,
  focus,
  onTopicReady,
  onItemCountChange,
}: IWidget) => {
  const [count, setCount] = useState<number>();
  const [currResId, setCurrResId] = useState<string>();
  return (
    <>
      <DiscussionTopicInfo
        topicId={topicId}
        childrenCount={count}
        onReady={(value: IComment) => {
          setCurrResId(value.resId);
          console.log("onReady", value);
          if (typeof onTopicReady !== "undefined") {
            onTopicReady(value);
          }
        }}
      />
      <DiscussionTopicChildren
        resId={currResId}
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
