import { useState } from "react";
import { Drawer } from "antd";
import CommentTopic from "./CommentTopic";
import CommentListCard, { TResType } from "./CommentListCard";
import { IComment } from "./CommentItem";

export interface IAnswerCount {
  id: string;
  count: number;
}
interface IWidget {
  trigger?: JSX.Element;
  resId?: string;
  resType?: TResType;
  onCommentCountChange?: Function;
}
const CommentBoxWidget = ({
  trigger,
  resId,
  resType,
  onCommentCountChange,
}: IWidget) => {
  const [open, setOpen] = useState(false);
  const [childrenDrawer, setChildrenDrawer] = useState(false);
  const [topicComment, setTopicComment] = useState<IComment>();
  const [answerCount, setAnswerCount] = useState<IAnswerCount>();

  const showChildrenDrawer = (
    e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
    comment: IComment
  ) => {
    setChildrenDrawer(true);
    setTopicComment(comment);
  };

  return (
    <>
      <span
        onClick={() => {
          setOpen(true);
        }}
      >
        {trigger}
      </span>
      <Drawer
        title="Discussion"
        width={520}
        onClose={() => {
          setOpen(false);
          if (document.getElementsByTagName("body")[0].hasAttribute("style")) {
            document.getElementsByTagName("body")[0].removeAttribute("style");
          }
        }}
        open={open}
        maskClosable={false}
      >
        <CommentListCard
          resId={resId}
          resType={resType}
          onSelect={showChildrenDrawer}
          changedAnswerCount={answerCount}
          onItemCountChange={(count: number) => {
            if (typeof onCommentCountChange !== "undefined") {
              onCommentCountChange(count);
            }
          }}
        />
        <Drawer
          title="回答"
          width={480}
          onClose={() => {
            setChildrenDrawer(false);
          }}
          open={childrenDrawer}
        >
          <CommentTopic
            topicId={topicComment?.id}
            onItemCountChange={(count: number, parent: string) => {
              setAnswerCount({ id: parent, count: count });
            }}
          />
        </Drawer>
      </Drawer>
    </>
  );
};

export default CommentBoxWidget;
