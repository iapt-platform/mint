import { Modal } from "antd";
import { useEffect, useState } from "react";
import AttachmentList from "./AttachmentList";
import { IAttachmentRequest } from "../api/Attachments";

interface IWidget {
  open?: boolean;
  trigger?: React.ReactNode;
  studioName?: string;
  onOpenChange?: Function;
  onSelect?: Function;
}
const AttachmentDialog = ({
  open,
  trigger,
  studioName,
  onOpenChange,
  onSelect,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);

  useEffect(() => setIsModalOpen(open), [open]);
  const showModal = () => {
    setIsModalOpen(true);
    if (typeof onOpenChange !== "undefined") {
      onOpenChange(true);
    }
  };

  const handleOk = () => {
    setIsModalOpen(false);
    if (typeof onOpenChange !== "undefined") {
      onOpenChange(false);
    }
  };

  const handleCancel = () => {
    setIsModalOpen(false);
    if (typeof onOpenChange !== "undefined") {
      onOpenChange(false);
    }
  };
  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={700}
        title="加入文集"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        maskClosable={false}
      >
        <AttachmentList
          studioName={studioName}
          onClick={(value: IAttachmentRequest) => {
            if (typeof onSelect !== "undefined") {
              onSelect(value);
            }
            handleOk();
          }}
        />
      </Modal>
    </>
  );
};

export default AttachmentDialog;
