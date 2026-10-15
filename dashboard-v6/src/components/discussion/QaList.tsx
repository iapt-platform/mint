import { useEffect, useState } from "react";

import DiscussionItem from "./DiscussionItem";
import {
  asDiscussionTarget,
  fetchTopics,
  type IComment,
  type TResType,
} from "../../api/discussion";

interface IWidget {
  resId?: string;
  resType?: TResType;
  onSelect?: (
    e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
    value: IComment
  ) => void;
}
/**
 * 某个对象上的问答（qa）话题，最早的在前。点开话题再看回答（由 QaBox 负责）。
 */
const QaListWidget = ({ resId, resType, onSelect }: IWidget) => {
  const [data, setData] = useState<IComment[]>();

  useEffect(() => {
    const target = asDiscussionTarget(resType);
    if (!resId || !target) {
      return;
    }
    fetchTopics({
      target_type: target,
      target_id: resId,
      type: "qa",
      sort: "created_at",
      per_page: 200,
    }).then((res) => setData(res.items));
  }, [resId, resType]);

  return (
    <>
      {data?.map((question) => (
        <DiscussionItem
          key={question.id}
          data={question}
          onSelect={(
            e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
            value: IComment
          ) => {
            onSelect?.(e, value);
          }}
        />
      ))}
    </>
  );
};

export default QaListWidget;
