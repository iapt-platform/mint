import { useEffect, useState } from "react";

import DiscussionTopicInfo from "./DiscussionTopicInfo";
import DiscussionTopicChildren from "./DiscussionTopicChildren";
import type { IComment, TDiscussionType, TResType } from "../../api/discussion";

interface IWidget {
  resType?: TResType;
  topicId?: string;
  topic?: IComment;
  focus?: string;
  hideTitle?: boolean;
  hideReply?: boolean;
  onItemCountChange?: (total: number, parentId?: string | null) => void;
  onTopicReady?: (value: IComment) => void;
  onTopicDelete?: (id?: string) => void;
  onConvert?: (value: TDiscussionType) => void;
}
const DiscussionTopicWidget = ({
  resType,
  topicId,
  topic,
  focus,
  hideTitle = false,
  hideReply = false,
  onTopicReady,
  onItemCountChange,
  onTopicDelete,
  onConvert,
}: IWidget) => {
  const [count, setCount] = useState<number>();
  const [currTopicId, setCurrTopicId] = useState(topicId);
  const [currTopic, setCurrTopic] = useState<IComment | undefined>(topic);
  useEffect(() => {
    setCurrTopic(topic);
  }, [topic]);

  return (
    <>
      <DiscussionTopicInfo
        topicId={currTopicId}
        topic={currTopic}
        hideTitle={hideTitle}
        childrenCount={count}
        onReady={(value: IComment) => {
          setCurrTopic(value);
          console.log("discussion onReady", value);
          onTopicReady?.(value);
        }}
        onDelete={onTopicDelete}
        onConvert={onConvert}
      />
      <DiscussionTopicChildren
        topic={currTopic}
        resId={currTopic?.resId}
        resType={resType}
        focus={focus}
        topicId={topicId}
        hideReply={hideReply}
        onItemCountChange={(total: number, parentId?: string | null) => {
          //把新建回答的消息传出去。
          setCount(total);
          onItemCountChange?.(total, parentId);
        }}
        onTopicCreate={(value: IComment) => {
          console.log("onTopicCreate", value);
          setCurrTopicId(value.id);
        }}
      />
    </>
  );
};

export default DiscussionTopicWidget;
