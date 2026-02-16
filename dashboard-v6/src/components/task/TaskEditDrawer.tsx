import { Button, Drawer, Space, Typography } from "antd";
import { useEffect, useState } from "react";

import { ITaskData } from "../api/task";
import Task from "./Task";
import { useIntl } from "react-intl";
import { fullUrl } from "../../utils";
import LikeAvatar from "../like/LikeAvatar";

const { Text } = Typography;

interface IWidget {
  taskId?: string;
  openDrawer?: boolean;
  onClose?: () => void;
  onChange?: (data: ITaskData[]) => void;
}
const TaskEditDrawer = ({
  taskId,
  openDrawer = false,
  onClose,
  onChange,
}: IWidget) => {
  const [open, setOpen] = useState(openDrawer);
  const intl = useIntl();

  useEffect(() => {
    setOpen(openDrawer);
  }, [openDrawer]);

  const onCloseDrawer = () => {
    setOpen(false);
    if (document.getElementsByTagName("body")[0].hasAttribute("style")) {
      document.getElementsByTagName("body")[0].removeAttribute("style");
    }
    if (onClose) {
      onClose();
    }
  };

  return (
    <>
      <Drawer
        title={""}
        placement={"right"}
        width={1000}
        onClose={onCloseDrawer}
        open={open}
        destroyOnClose={true}
        footer={
          <Space>
            <Text>关注</Text>
            <LikeAvatar resId={taskId} resType="task" type="watch" />
          </Space>
        }
        extra={
          <Button
            type="link"
            onClick={() => {
              window.open(fullUrl(`/article/task/${taskId}`), "_blank");
            }}
          >
            {intl.formatMessage(
              {
                id: "buttons.open.in.new.tab",
              },
              { item: intl.formatMessage({ id: "labels.task" }) }
            )}
          </Button>
        }
      >
        <Task taskId={taskId} onChange={onChange} />
      </Drawer>
    </>
  );
};

export default TaskEditDrawer;
