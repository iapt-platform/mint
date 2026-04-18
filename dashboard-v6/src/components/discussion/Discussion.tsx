import { useState } from "react";
import { ArrowLeftOutlined } from "@ant-design/icons";

import DiscussionTopic from "./DiscussionTopic";
import DiscussionListCard from "./DiscussionListCard";

import { countChange } from "../../reducers/discussion";
import { Button, Space, Typography } from "antd";
import store from "../../store";
import type { IComment, TDiscussionType, TResType } from "../../api/discussion";

const { Text } = Typography;

export interface IAnswerCount {
  id: string;
  count: number;
}

interface IWidget {
  resId?: string;
  resType?: TResType;
  showTopicId?: string;
  focus?: string;
  type?: TDiscussionType;
  showStudent?: boolean;
  onTopicReady?: (value: IComment) => void;
}

const Discussion = ({
  resId,
  resType,
  showTopicId,
  showStudent = false,
  focus,
  type = "discussion",
  onTopicReady,
}: IWidget) => {
  // 用户手动点击触发的状态
  const [manualTopicId, setManualTopicId] = useState<string>();
  const [manualTopic, setManualTopic] = useState<IComment>();
  const [manualOpen, setManualOpen] = useState(false);

  // 用于检测 resId 变化时重置
  const [prevResId, setPrevResId] = useState(resId);
  if (prevResId !== resId) {
    setPrevResId(resId);
    setManualOpen(false);
    setManualTopicId(undefined);
    setManualTopic(undefined);
  }

  const [answerCount, setAnswerCount] = useState<IAnswerCount>();
  const [topicTitle, setTopicTitle] = useState<string>();

  // 完全派生，不需要 effect
  const childrenDrawer = manualOpen || !!showTopicId;
  const topicId = manualOpen ? manualTopicId : showTopicId;
  const topic = manualOpen ? manualTopic : undefined;

  const showChildrenDrawer = (comment: IComment) => {
    console.debug("discussion comment", comment);
    setManualOpen(true);
    if (comment.id) {
      setManualTopicId(comment.id);
      setManualTopic(undefined);
    } else {
      setManualTopicId(undefined);
      setManualTopic(comment);
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
              onClick={() => setManualOpen(false)}
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
            onItemCountChange={(total: number, parentId?: string | null) => {
              if (parentId) {
                setAnswerCount({ id: parentId, count: total });
              }
            }}
            onTopicReady={(value: IComment) => {
              setTopicTitle(value.title);
              if (typeof onTopicReady !== "undefined") {
                onTopicReady(value);
              }
            }}
            onTopicDelete={() => setManualOpen(false)}
            onConvert={() => setManualOpen(false)}
          />
        </div>
      ) : (
        <DiscussionListCard
          resId={resId}
          resType={resType}
          type={type}
          showStudent={showStudent}
          onSelect={(
            _e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
            comment: IComment
          ) => showChildrenDrawer(comment)}
          onReply={(comment: IComment) => showChildrenDrawer(comment)}
          onReady={() => {}}
          changedAnswerCount={answerCount}
          onItemCountChange={(count: number) => {
            store.dispatch(countChange({ count, resId, resType }));
          }}
        />
      )}
    </>
  );
};
export default Discussion;
