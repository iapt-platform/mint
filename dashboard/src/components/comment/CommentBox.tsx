import { useState } from "react";
import { Button, Drawer } from "antd";
import CommentTopic from "./CommentTopic";
import CommentListCard from "./CommentListCard";

interface IWidget {
  trigger?: JSX.Element;
}
const Widget = ({ trigger }: IWidget) => {
  const [open, setOpen] = useState(false);
  const [childrenDrawer, setChildrenDrawer] = useState(false);

  const showDrawer = () => {
    setOpen(true);
  };

  const onClose = () => {
    setOpen(false);
  };

  const showChildrenDrawer = () => {
    setChildrenDrawer(true);
  };

  const onChildrenDrawerClose = () => {
    setChildrenDrawer(false);
  };

  return (
    <>
      <span onClick={showDrawer}>{trigger}</span>
      <Drawer
        title="Multi-level drawer"
        width={520}
        closable={false}
        onClose={onClose}
        open={open}
      >
        <Button type="primary" onClick={showChildrenDrawer}>
          Two-level drawer
        </Button>
        <CommentListCard resId="" onSelect={showChildrenDrawer} />
        <Drawer
          title="Two-level Drawer"
          width={480}
          closable={false}
          onClose={onChildrenDrawerClose}
          open={childrenDrawer}
        >
          <CommentTopic resId="" />
        </Drawer>
      </Drawer>
    </>
  );
};

export default Widget;
