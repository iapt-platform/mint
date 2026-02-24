import { Modal } from "antd";
import NissayaAligner from "./NissayaAligner";
import { useEffect, useState, type JSX } from "react";
import type { IChannel } from "../channel/Channel";

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
  const [isModalOpen, setIsModalOpen] = useState(open);

  useEffect(() => {
    setIsModalOpen(open);
  }, [open]);

  const showModal = () => {
    setIsModalOpen(true);
  };

  const modalClose = () => {
    setIsModalOpen(false);
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
