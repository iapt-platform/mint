import { useState } from "react";
import { Drawer } from "antd";
import CommentTopic from "./CommentTopic";
import CommentListCard from "./CommentListCard";
import { IComment } from "./CommentItem";

interface IWidget {
  trigger?: JSX.Element;
  resId?: string;
  resType?: string;
  onCommentCountChange?: Function;
}
const Widget = ({ trigger, resId, resType, onCommentCountChange }: IWidget) => {
  const [open, setOpen] = useState(false);
  const [childrenDrawer, setChildrenDrawer] = useState(false);
  const [topicComment, setTopicComment] = useState<IComment>();
  console.log(resId, resType);
  const showDrawer = () => {
    setOpen(true);
  };

  const onClose = () => {
    setOpen(false);
  };

  const showChildrenDrawer = (
    e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
    comment: IComment
  ) => {
    setChildrenDrawer(true);
    setTopicComment(comment);
  };

  const onChildrenDrawerClose = () => {
    setChildrenDrawer(false);
  };

  return (
    <>
      <span onClick={showDrawer}>{trigger}</span>
      <Drawer
        title="Discussion"
        width={520}
        closable={false}
        onClose={onClose}
        open={open}
      >
        <CommentListCard
          resId={resId}
          resType={resType}
          onSelect={showChildrenDrawer}
          onItemCountChange={(count: number) => {
            if (typeof onCommentCountChange !== "undefined") {
              onCommentCountChange(count);
            }
          }}
        />
        <Drawer
          title="Two-level Drawer"
          width={480}
          closable={false}
          onClose={onChildrenDrawerClose}
          open={childrenDrawer}
        >
          <CommentTopic comment={topicComment} resId="" />
        </Drawer>
      </Drawer>
    </>
  );
};

export default Widget;
