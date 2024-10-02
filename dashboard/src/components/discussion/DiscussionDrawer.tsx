import { useState } from "react";
import { Button, Divider, Drawer, Space } from "antd";
import { FullscreenOutlined, FullscreenExitOutlined } from "@ant-design/icons";

import DiscussionTopic from "./DiscussionTopic";
import DiscussionListCard, { TResType } from "./DiscussionListCard";
import { IComment } from "./DiscussionItem";
import DiscussionAnchor from "./DiscussionAnchor";
import { Link } from "react-router-dom";
import { useIntl } from "react-intl";

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
const DiscussionDrawerWidget = ({
  trigger,
  resId,
  resType,
  onCommentCountChange,
}: IWidget) => {
  const intl = useIntl();
  const [open, setOpen] = useState(false);
  const [childrenDrawer, setChildrenDrawer] = useState(false);
  const [topicComment, setTopicComment] = useState<IComment>();
  const [answerCount, setAnswerCount] = useState<IAnswerCount>();
  const drawerMinWidth = 720;
  const drawerMaxWidth = 1100;

  const [drawerWidth, setDrawerWidth] = useState(drawerMinWidth);
  const showChildrenDrawer = (comment: IComment) => {
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
        destroyOnClose
        extra={
          <Space>
            <Link to={`/discussion/show/${resType}/${resId}`} target="_blank">
              {intl.formatMessage({
                id: "buttons.open.in.new.tab",
              })}
            </Link>
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
        <DiscussionListCard
          resId={resId}
          resType={resType}
          onSelect={(
            e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
            comment: IComment
          ) => showChildrenDrawer(comment)}
          onReply={(comment: IComment) => showChildrenDrawer(comment)}
          changedAnswerCount={answerCount}
          onItemCountChange={(count: number) => {
            if (typeof onCommentCountChange !== "undefined") {
              onCommentCountChange(count);
            }
          }}
        />
        <Drawer
          title="Answer"
          width={700}
          onClose={() => {
            setChildrenDrawer(false);
          }}
          open={childrenDrawer}
        >
          <DiscussionTopic
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

export default DiscussionDrawerWidget;
