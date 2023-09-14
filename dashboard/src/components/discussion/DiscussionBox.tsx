import { useState } from "react";
import { ArrowLeftOutlined } from "@ant-design/icons";

import DiscussionTopic from "./DiscussionTopic";
import DiscussionListCard from "./DiscussionListCard";
import { IComment } from "./DiscussionItem";
import { useAppSelector } from "../../hooks";
import { countChange, message, showAnchor } from "../../reducers/discussion";
import { Button } from "antd";
import store from "../../store";

export interface IAnswerCount {
  id: string;
  count: number;
}

const DiscussionBoxWidget = () => {
  const [childrenDrawer, setChildrenDrawer] = useState(false);
  const [topicComment, setTopicComment] = useState<IComment>();
  const [answerCount, setAnswerCount] = useState<IAnswerCount>();

  const discussionMessage = useAppSelector(message);

  const showChildrenDrawer = (comment: IComment) => {
    setChildrenDrawer(true);
    setTopicComment(comment);
  };

  return (
    <>
      <Button
        type="link"
        onClick={() => {
          store.dispatch(
            showAnchor({
              type: "discussion",
              resId: discussionMessage?.resId,
              resType: discussionMessage?.resType,
            })
          );
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
            resId={discussionMessage?.resId}
            resType={discussionMessage?.resType}
            topicId={topicComment?.id}
            onItemCountChange={(count: number, parent: string) => {
              setAnswerCount({ id: parent, count: count });
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
