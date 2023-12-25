import { useEffect, useState } from "react";
import { ArrowLeftOutlined } from "@ant-design/icons";

import DiscussionTopic from "./DiscussionTopic";
import DiscussionListCard, { TResType } from "./DiscussionListCard";
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

interface IWidget {
  resId?: string;
  resType: TResType;
  focus?: string;
}

const DiscussionWidget = ({ resId, resType, focus }: IWidget) => {
  const [childrenDrawer, setChildrenDrawer] = useState(false);
  const [topicId, setTopicId] = useState<string>();
  const [topic, setTopic] = useState<IComment>();
  const [answerCount, setAnswerCount] = useState<IAnswerCount>();
  const [currTopic, setCurrTopic] = useState<IComment>();

  const showChildrenDrawer = (comment: IComment) => {
    setChildrenDrawer(true);
    if (comment.id) {
      setTopicId(comment.id);
      setTopic(undefined);
    } else {
      setTopicId(undefined);
      setTopic(comment);
    }
  };

  return (
    <>
      {childrenDrawer ? (
        <div>
          <Button
            shape="circle"
            icon={<ArrowLeftOutlined />}
            onClick={() => setChildrenDrawer(false)}
          />
          <DiscussionTopic
            resType={resType}
            topicId={topicId}
            topic={topic}
            focus={focus}
            onItemCountChange={(count: number, parent: string) => {
              setAnswerCount({ id: parent, count: count });
            }}
            onTopicReady={(value: IComment) => {
              setCurrTopic(value);
            }}
            onTopicDelete={() => {
              setChildrenDrawer(false);
            }}
          />
        </div>
      ) : (
        <DiscussionListCard
          resId={resId}
          resType={resType}
          onSelect={(
            e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
            comment: IComment
          ) => showChildrenDrawer(comment)}
          onReply={(comment: IComment) => showChildrenDrawer(comment)}
          onReady={() => {}}
          changedAnswerCount={answerCount}
          onItemCountChange={(count: number) => {
            store.dispatch(
              countChange({
                count: count,
                resId: resId,
                resType: resType,
              })
            );
          }}
        />
      )}
    </>
  );
};

export default DiscussionWidget;
