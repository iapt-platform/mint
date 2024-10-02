import { useEffect, useState } from "react";
import { ArrowLeftOutlined } from "@ant-design/icons";

import DiscussionTopic from "./DiscussionTopic";
import { TResType } from "./DiscussionListCard";
import { IComment } from "./DiscussionItem";

import { Button, Space, Typography } from "antd";
import { TDiscussionType } from "./Discussion";
import QaList from "./QaList";

const { Text } = Typography;

interface IWidget {
  resId?: string;
  resType?: TResType;
  showTopicId?: string;
  focus?: string;
  onTopicReady?: Function;
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
            onConvert={(value: TDiscussionType) => {
              setChildrenDrawer(false);
            }}
          />
        </div>
      ) : (
        <QaList
          resId={resId}
          resType={resType}
          onSelect={(
            e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
            comment: IComment
          ) => showChildrenDrawer(comment)}
          onReply={(comment: IComment) => showChildrenDrawer(comment)}
        />
      )}
    </>
  );
};

export default DiscussionWidget;
