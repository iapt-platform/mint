import { Modal } from "antd";
import { useState } from "react";
import AttachmentList from "./AttachmentList";
import type { IAttachmentRequest } from "../../api/Attachments";

interface IWidget {
  open?: boolean;
  trigger?: React.ReactNode;
  studioName?: string;
  onOpenChange?: (open: boolean) => void;
  onSelect?: (value: IAttachmentRequest) => void;
}
const AttachmentDialog = ({
  open,
  trigger,
  studioName,
  onOpenChange,
  onSelect,
}: IWidget) => {
  const [innerOpen, setInnerOpen] = useState(false);

  const isModalOpen = open ?? innerOpen;

  const showModal = () => {
    setInnerOpen(true);
    if (typeof onOpenChange !== "undefined") {
      onOpenChange(true);
    }
  };

  const handleOk = () => {
    setInnerOpen(false);
    if (typeof onOpenChange !== "undefined") {
      onOpenChange(false);
    }
  };

  const handleCancel = () => {
    setInnerOpen(false);
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
