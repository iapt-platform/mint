import { useEffect, useState } from "react";
import { ArrowLeftOutlined } from "@ant-design/icons";

import DiscussionTopic from "./DiscussionTopic";
import DiscussionListCard from "./DiscussionListCard";
import { IComment } from "./DiscussionItem";
import { useAppSelector } from "../../hooks";
import {
  countChange,
  IShowDiscussion,
  message,
  show,
  showAnchor,
} from "../../reducers/discussion";
import { Button } from "antd";
import store from "../../store";

export interface IAnswerCount {
  id: string;
  count: number;
}

const DiscussionBoxWidget = () => {
  const [childrenDrawer, setChildrenDrawer] = useState(false);
  const [topicId, setTopicId] = useState<string>();
  const [answerCount, setAnswerCount] = useState<IAnswerCount>();
  const [currTopic, setCurrTopic] = useState<IComment>();

  const discussionMessage = useAppSelector(message);

  useEffect(() => {
    if (discussionMessage?.topic) {
      setChildrenDrawer(true);
      setTopicId(discussionMessage?.topic);
    }
  }, [discussionMessage]);

  const showChildrenDrawer = (comment: IComment) => {
    setChildrenDrawer(true);
    setTopicId(comment.id);
  };

  return (
    <>
      <Button
        type="link"
        onClick={() => {
          const anchorInfo: IShowDiscussion = {
            type: "discussion",
            resId: discussionMessage?.resId
              ? discussionMessage?.resId
              : currTopic?.resId,
            resType: discussionMessage?.resType,
          };
          store.dispatch(show(anchorInfo));
          store.dispatch(showAnchor(anchorInfo));
        }}
      >
        显示译文
      </Button>
      {childrenDrawer ? (
        <div>
          <Button
            shape="circle"
            icon={<ArrowLeftOutlined />}
            onClick={() => setChildrenDrawer(false)}
          />
          <DiscussionTopic
            resType={discussionMessage?.resType}
            topicId={topicId}
            focus={discussionMessage?.comment}
            onItemCountChange={(count: number, parent: string) => {
              setAnswerCount({ id: parent, count: count });
            }}
            onTopicReady={(value: IComment) => {
              setCurrTopic(value);
            }}
          />
        </div>
      ) : (
        <DiscussionListCard
          resId={discussionMessage?.resId}
          resType={discussionMessage?.resType}
          onSelect={(
            e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
            comment: IComment
          ) => showChildrenDrawer(comment)}
          onReply={(comment: IComment) => showChildrenDrawer(comment)}
          changedAnswerCount={answerCount}
          onItemCountChange={(count: number) => {
            store.dispatch(
              countChange({
                count: count,
                resId: discussionMessage?.resId,
                resType: discussionMessage?.resType,
              })
            );
          }}
        />
      )}
    </>
  );
};

export default DiscussionBoxWidget;
