import React, { useEffect, useState } from "react";
import { Button, Modal } from "antd";

import ChannelPickerTable from "./ChannelPickerTable";
import { IChannel } from "./Channel";
import { ArticleType } from "../article/Article";

interface IWidget {
  trigger?: React.ReactNode;
  type?: ArticleType | "editable";
  articleId?: string;
  multiSelect?: boolean;
  open?: boolean;
  onClose?: Function;
  onSelect?: Function;
}
const ChannelPickerWidget = ({
  trigger,
  type,
  articleId,
  multiSelect,
  open = false,
  onClose,
  onSelect,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);

  useEffect(() => {
    setIsModalOpen(open);
  }, [open]);
  const showModal = () => {
    setIsModalOpen(true);
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
        width={"80%"}
        title="选择版本风格"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <ChannelPickerTable
          type={type}
          articleId={articleId}
          multiSelect={true}
          onSelect={(channels: IChannel[]) => {
            console.log(channels);
            handleCancel();
            if (typeof onClose !== "undefined") {
              onClose();
            }
            if (typeof onSelect !== "undefined") {
              onSelect(channels);
            }
          }}
        />
      </Modal>
    </>
  );
};

export default ChannelPickerWidget;
