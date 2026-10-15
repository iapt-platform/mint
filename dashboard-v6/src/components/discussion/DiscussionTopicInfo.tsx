import { useEffect, useState } from "react";

import DiscussionItem from "./DiscussionItem";

import {
  fetchDiscussion,
  type IComment,
  type TDiscussionType,
} from "../../api/discussion";

interface IWidget {
  topicId?: string;
  topic?: IComment;
  childrenCount?: number;
  hideTitle?: boolean;
  onDelete?: (id?: string) => void;
  onClose?: (data: IComment) => void;
  onReady?: (discussion: IComment) => void;
  onConvert?: (value: TDiscussionType) => void;
}
const DiscussionTopicInfoWidget = ({
  topicId,
  topic,
  childrenCount,
  hideTitle = false,
  onReady,
  onDelete,
  onClose,
  onConvert,
}: IWidget) => {
  const [data, setData] = useState<IComment | undefined>(topic);
  useEffect(() => {
    setData(topic);
  }, [topic]);
  useEffect(() => {
    setData((origin) => {
      if (typeof origin !== "undefined") {
        origin.childrenCount = childrenCount;
        return origin;
      }
    });
  }, [childrenCount]);

  useEffect(() => {
    // 调用方已经带着这条话题来了（如话题页），不再重复取
    if (typeof topicId === "undefined" || topic?.id === topicId) {
      return;
    }
    fetchDiscussion(topicId).then((discussion) => {
      setData(discussion);
      onReady?.(discussion);
    });
  }, [topicId]);

  return (
    <div>
      {data ? (
        <DiscussionItem
          data={data}
          hideTitle={hideTitle}
          onDelete={() => {
            onDelete?.(data.id);
          }}
          onClose={() => {
            onClose?.(data);
          }}
          onConvert={onConvert}
        />
      ) : (
        <></>
      )}
    </div>
  );
};

export default DiscussionTopicInfoWidget;
