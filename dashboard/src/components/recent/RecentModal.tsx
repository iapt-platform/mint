import React, { useEffect, useState } from "react";
import { Modal } from "antd";
import RecentList, { IRecent } from "./RecentList";
import { useIntl } from "react-intl";

interface IWidget {
  trigger?: React.ReactNode;
  open?: boolean;
  onSelect?: Function;
  onOpen?: Function;
}
const RecentModalWidget = ({
  trigger,
  open = false,
  onSelect,
  onOpen,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);
  const intl = useIntl();

  useEffect(() => {
    setIsModalOpen(open);
    if (typeof onOpen !== "undefined") {
      onOpen(open);
    }
  }, [open]);
  const showModal = () => {
    setIsModalOpen(true);
    if (typeof onOpen !== "undefined") {
      onOpen(true);
    }
  };

  const handleOk = () => {
    setIsModalOpen(false);
    if (typeof onOpen !== "undefined") {
      onOpen(false);
    }
  };

  const handleCancel = () => {
    setIsModalOpen(false);
    if (typeof onOpen !== "undefined") {
      onOpen(false);
    }
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={"80%"}
        title={intl.formatMessage({
          id: `labels.recent-scan`,
        })}
        footer={false}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        destroyOnClose
      >
        <RecentList
          onSelect={(
            event: React.MouseEvent<HTMLElement, MouseEvent>,
            param: IRecent
          ) => {
            if (typeof onSelect !== "undefined") {
              onSelect(event, param);
            }
          }}
        />
      </Modal>
    </>
  );
};

export default RecentModalWidget;
