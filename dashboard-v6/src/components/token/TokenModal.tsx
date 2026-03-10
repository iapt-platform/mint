import { useState } from "react";
import { Modal } from "antd";

import Token from "./Token";
import type { ArticleType } from "../../api/article";

interface IWidget {
  channelId?: string;
  articleId?: string;
  type?: ArticleType;
  trigger?: React.ReactNode;
  open?: boolean;
  onClose?: () => void;
}
const TokenModal = ({
  channelId,
  articleId,
  type,
  trigger,
  open,
  onClose,
}: IWidget) => {
  const [innerOpen, setInnerOpen] = useState(false);
  const isModalOpen = open ?? innerOpen;

  const showModal = () => {
    setInnerOpen(true);
  };

  const handleOk = () => {
    if (typeof onClose !== "undefined") {
      onClose();
    }
    setInnerOpen(false);
  };

  const handleCancel = () => {
    if (typeof onClose !== "undefined") {
      onClose();
    }
    setInnerOpen(false);
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={500}
        title="token"
        footer={false}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <Token channelId={channelId} articleId={articleId} type={type} />
      </Modal>
    </>
  );
};

export default TokenModal;
