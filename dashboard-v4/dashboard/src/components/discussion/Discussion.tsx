import { useEffect, useState } from "react";
import { ArrowLeftOutlined } from "@ant-design/icons";

import DiscussionTopic from "./DiscussionTopic";
import DiscussionListCard, { TResType } from "./DiscussionListCard";
import { IComment } from "./DiscussionItem";

import { countChange } from "../../reducers/discussion";
import { Button, Space, Typography } from "antd";
import store from "../../store";

const { Text } = Typography;

export interface IAnswerCount {
  id: string;
  count: number;
}
export type TDiscussionType = "qa" | "discussion" | "help" | "comment";

interface IWidget {
  resId?: string;
  resType?: TResType;
  showTopicId?: string;
  focus?: string;
  type?: TDiscussionType;
  showStudent?: boolean;
  onTopicReady?: Function;
}

const DiscussionWidget = ({
  resId,
  resType,
  showTopicId,
  showStudent = false,
  focus,
  type = "discussion",
  onTopicReady,
}: IWidget) => {
  const [childrenDrawer, setChildrenDrawer] = useState(false);
  const [topicId, setTopicId] = useState<string>();
  const [topic, setTopic] = useState<IComment>();
  const [answerCount, setAnswerCount] = useState<IAnswerCount>();
  const [topicTitle, setTopicTitle] = useState<string>();

  useEffect(() => {
    if (showTopicId) {
      setChildrenDrawer(true);
      setTopicId(showTopicId);
    } else {
      setChildrenDrawer(false);
    }
  }, [showTopicId]);

  const showChildrenDrawer = (comment: IComment) => {
    console.debug("discussion comment", comment);
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
          <Space>
            <Button
              shape="circle"
              icon={<ArrowLeftOutlined />}
              onClick={() => setChildrenDrawer(false)}
            />
            <Text strong style={{ fontSize: 16 }}>
              {topic ? topic.title : topicTitle}
            </Text>
          </Space>
          <DiscussionTopic
            resType={resType}
            topicId={topicId}
            topic={topic}
            focus={focus}
            hideTitle
            onItemCountChange={(count: number, parent: string) => {
              setAnswerCount({ id: parent, count: count });
            }}
            onTopicReady={(value: IComment) => {
              setTopicTitle(value.title);
              if (typeof onTopicReady !== "undefined") {
                onTopicReady(value);
              }
            }}
            onTopicDelete={() => {
              setChildrenDrawer(false);
            }}
            onConvert={(value: TDiscussionType) => {
              setChildrenDrawer(false);
            }}
          />
        </div>
      ) : (
        <DiscussionListCard
          resId={resId}
          resType={resType}
          type={type}
          showStudent={showStudent}
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
