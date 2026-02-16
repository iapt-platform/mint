import { useEffect, useState } from "react";
import { Button, Divider, Drawer, Space } from "antd";
import { FullscreenOutlined, FullscreenExitOutlined } from "@ant-design/icons";

import DiscussionTopic from "./DiscussionTopic";
import DiscussionListCard, { TResType } from "./DiscussionListCard";
import { IComment } from "./DiscussionItem";
import DiscussionAnchor from "./DiscussionAnchor";
import { Link } from "react-router-dom";
import { useIntl } from "react-intl";
import Discussion from "./Discussion";

export interface IAnswerCount {
  id: string;
  count: number;
}
interface IWidget {
  trigger?: JSX.Element;
  open?: boolean;
  onClose?: () => void;
  resId?: string;
  resType?: TResType;
}
const DiscussionDrawerWidget = ({
  trigger,
  open,
  onClose,
  resId,
  resType,
}: IWidget) => {
  const intl = useIntl();
  const [openDrawer, setOpenDrawer] = useState(open);

  useEffect(() => {
    setOpenDrawer(open);
  }, [open]);

  const drawerMinWidth = 600;
  const drawerMaxWidth = 1100;

  const [drawerWidth, setDrawerWidth] = useState(drawerMinWidth);

  return (
    <>
      <span
        onClick={() => {
          setOpenDrawer(true);
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
              {intl.formatMessage(
                {
                  id: "buttons.open.in.new.tab",
                },
                { item: "" }
              )}
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
          if (onClose) {
            onClose();
          } else {
            setOpenDrawer(false);
          }

          if (document.getElementsByTagName("body")[0].hasAttribute("style")) {
            document.getElementsByTagName("body")[0].removeAttribute("style");
          }
        }}
        open={openDrawer}
        maskClosable={false}
      >
        <Discussion resId={resId} resType={resType} showStudent={false} />
      </Drawer>
    </>
  );
};

export default DiscussionDrawerWidget;
