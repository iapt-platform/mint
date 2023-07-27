import { useState } from "react";
import { Button, Divider, Drawer, Space } from "antd";
import { FullscreenOutlined, FullscreenExitOutlined } from "@ant-design/icons";

import CommentTopic from "./DiscussionTopic";
import CommentListCard, { TResType } from "./DiscussionListCard";
import { IComment } from "./DiscussionItem";
import DiscussionAnchor from "./DiscussionAnchor";

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
const DiscussionBoxWidget = ({
  trigger,
  resId,
  resType,
  onCommentCountChange,
}: IWidget) => {
  const [open, setOpen] = useState(false);
  const [childrenDrawer, setChildrenDrawer] = useState(false);
  const [topicComment, setTopicComment] = useState<IComment>();
  const [answerCount, setAnswerCount] = useState<IAnswerCount>();
  const drawerMinWidth = 720;
  const drawerMaxWidth = 1100;

  const [drawerWidth, setDrawerWidth] = useState(drawerMinWidth);
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
        extra={
          <Space>
            {drawerWidth === drawerMinWidth ? (
              <Button
                type="link"
                icon={<FullscreenOutlined />}
                onClick={() => setDrawerWidth(drawerMaxWidth)}
              />
            ) : (
              <Button
                type="link"
                icon={<FullscreenExitOutlined />}
                onClick={() => setDrawerWidth(drawerMinWidth)}
              />
            )}
          </Space>
        }
        width={drawerWidth}
        onClose={() => {
          setOpen(false);
          if (document.getElementsByTagName("body")[0].hasAttribute("style")) {
            document.getElementsByTagName("body")[0].removeAttribute("style");
          }
        }}
        open={open}
        maskClosable={false}
      >
        <DiscussionAnchor resId={resId} resType={resType} />
        <Divider></Divider>
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
          title="Answer"
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

export default DiscussionBoxWidget;
