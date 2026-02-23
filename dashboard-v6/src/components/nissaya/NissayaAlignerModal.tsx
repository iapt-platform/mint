import { Modal } from "antd";
import NissayaAligner from "./NissayaAligner";
import { useState, type JSX } from "react";
import type { IChannel } from "../../api/Channel";

interface IWidget {
  trigger?: JSX.Element | string;
  sentencesId?: string[];
  channel?: IChannel;
  open?: boolean;
  onClose?: () => void;
}

const NissayaAlignerModal = ({
  trigger,
  sentencesId,
  open,
  onClose,
}: IWidget) => {
  const [innerOpen, setInnerOpen] = useState(false);
  const isModalOpen = open ?? innerOpen;

  const showModal = () => {
    setInnerOpen(true);
  };

  const modalClose = () => {
    setInnerOpen(false);
    if (onClose) {
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
        destroyOnHidden={true}
        footer={[]}
      >
        <NissayaAligner sentencesId={sentencesId} />
      </Modal>
    </>
  );
};

export default NissayaAlignerModal;
