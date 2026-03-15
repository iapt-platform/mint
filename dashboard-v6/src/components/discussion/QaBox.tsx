import { useEffect, useState } from "react";
import { ArrowLeftOutlined } from "@ant-design/icons";

import DiscussionTopic from "./DiscussionTopic";

import { Button, Space, Typography } from "antd";

import QaList from "./QaList";
import type { IComment, TResType } from "../../api/discussion";

const { Text } = Typography;

interface IWidget {
  resId?: string;
  resType?: TResType;
  showTopicId?: string;
  focus?: string;
  onTopicReady?: (value: IComment) => void;
}

const DiscussionWidget = ({
  resId,
  resType,
  showTopicId,
  focus,
  onTopicReady,
}: IWidget) => {
  const [childrenDrawer, setChildrenDrawer] = useState(false);
  const [topicId, setTopicId] = useState<string>();
  const [topic, setTopic] = useState<IComment>();
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
            onTopicReady={(value: IComment) => {
              setTopicTitle(value.title);
              if (typeof onTopicReady !== "undefined") {
                onTopicReady(value);
              }
            }}
            onTopicDelete={() => {
              setChildrenDrawer(false);
            }}
            onConvert={() => {
              setChildrenDrawer(false);
            }}
          />
        </div>
      ) : (
        <QaList
          resId={resId}
          resType={resType}
          onSelect={(
            _e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
            comment: IComment
          ) => showChildrenDrawer(comment)}
        />
      )}
    </>
  );
};

export default DiscussionWidget;
