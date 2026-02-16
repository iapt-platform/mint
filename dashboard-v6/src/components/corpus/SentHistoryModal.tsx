import React, { useEffect, useState } from "react";
import { Modal } from "antd";
import { useIntl } from "react-intl";

import SentHistory from "./SentHistory";

interface IWidget {
  sentId?: string;
  trigger?: React.ReactNode;
  open?: boolean;
  onClose?: Function;
}
const SentHistoryModalWidget = ({
  open = false,
  sentId,
  trigger,
  onClose,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);
  const intl = useIntl();

  useEffect(() => setIsModalOpen(open), [open]);

  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    setIsModalOpen(false);
    if (typeof onClose !== "undefined") {
      onClose();
    }
  };

  const handleCancel = () => {
    setIsModalOpen(false);
    if (typeof onClose !== "undefined") {
      onClose();
    }
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        style={{ top: 20 }}
        width={"80%"}
        title={intl.formatMessage({
          id: "buttons.timeline",
        })}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        destroyOnClose
      >
        <SentHistory sentId={sentId} />
      </Modal>
    </>
  );
};

export default SentHistoryModalWidget;
