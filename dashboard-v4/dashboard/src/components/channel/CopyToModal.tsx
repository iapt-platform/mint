import { useEffect, useState } from "react";
import { Modal } from "antd";

import CopyToStep from "./CopyToStep";
import { IChannel } from "./Channel";

interface IWidget {
  trigger?: JSX.Element | string;
  channel?: IChannel;
  sentencesId?: string[];
  open?: boolean;
  important?: boolean;
  onClose?: Function;
}
const CopyToModalWidget = ({
  trigger,
  channel,
  sentencesId,
  open,
  important = false,
  onClose,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);
  const [initStep, setInitStep] = useState(0);

  useEffect(() => setIsModalOpen(open), [open]);

  const showModal = () => {
    setIsModalOpen(true);
    setInitStep(0);
  };

  const modalClose = () => {
    setIsModalOpen(false);
    if (typeof onClose !== "undefined") {
      onClose();
    }
  };
  const handleOk = () => {
    modalClose();
  };

  const handleCancel = () => {
    modalClose();
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={"95%"}
        style={{ maxWidth: 1500 }}
        title="版本间复制"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        destroyOnClose={true}
        footer={[]}
      >
        <CopyToStep
          initStep={initStep}
          channel={channel}
          sentencesId={sentencesId}
          important={important}
          onClose={() => {
            setIsModalOpen(false);
            Modal.destroyAll();
          }}
        />
      </Modal>
    </>
  );
};

export default CopyToModalWidget;
