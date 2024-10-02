import { useEffect, useState } from "react";

import { IComment } from "./DiscussionItem";
import { useAppSelector } from "../../hooks";
import {
  IShowDiscussion,
  message,
  show,
  showAnchor,
} from "../../reducers/discussion";
import { Button } from "antd";
import store from "../../store";
import Discussion from "./Discussion";

export interface IAnswerCount {
  id: string;
  count: number;
}

interface IWidget {
  onTopicChange?: Function;
}

const DiscussionBoxWidget = ({ onTopicChange }: IWidget) => {
  const [topicId, setTopicId] = useState<string>();
  const [currTopic, setCurrTopic] = useState<IComment>();

  const discussionMessage = useAppSelector(message);

  useEffect(() => {
    if (discussionMessage) {
      if (discussionMessage.topic) {
        setTopicId(discussionMessage.topic);
      } else {
      }
    }
  }, [discussionMessage]);

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
      <Discussion
        resId={discussionMessage?.resId}
        resType={discussionMessage?.resType}
        focus={discussionMessage?.comment}
        showStudent={discussionMessage?.withStudent}
        showTopicId={topicId}
        onTopicReady={(value: IComment) => {
          setCurrTopic(value);
        }}
      />
    </>
  );
};

export default DiscussionBoxWidget;
