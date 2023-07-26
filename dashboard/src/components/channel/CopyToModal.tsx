import { useState } from "react";
import { Modal } from "antd";

import CopyToStep from "./CopyToStep";
import { IChannel } from "./Channel";

interface IWidget {
  trigger: JSX.Element | string;
  channel?: IChannel;
}
const CopyToModalWidget = ({ trigger, channel }: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [initStep, setInitStep] = useState(0);

  const showModal = () => {
    setIsModalOpen(true);
    setInitStep(0);
  };

  const handleOk = () => {
    setIsModalOpen(false);
  };

  const handleCancel = () => {
    setIsModalOpen(false);
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
        footer={[]}
      >
        <CopyToStep
          initStep={initStep}
          channel={channel}
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
