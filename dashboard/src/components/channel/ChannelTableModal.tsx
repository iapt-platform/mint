import React, { useEffect, useState } from "react";
import { Modal } from "antd";

import { ArticleType } from "../article/Article";
import ChannelTable from "./ChannelTable";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { IChannel } from "./Channel";

interface IWidget {
  trigger?: React.ReactNode;
  type?: ArticleType | "editable";
  articleId?: string;
  multiSelect?: boolean;
  open?: boolean;
  onClose?: Function;
  onSelect?: Function;
}
const ChannelTableModalWidget = ({
  trigger,
  type,
  articleId,
  multiSelect = true,
  open = false,
  onClose,
  onSelect,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);
  const user = useAppSelector(_currentUser);

  useEffect(() => {
    setIsModalOpen(open);
  }, [open]);
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
        width={"80%"}
        title="选择版本风格"
        footer={false}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <ChannelTable
          studioName={user?.realName}
          type={type}
          onSelect={(channel: IChannel) => {
            handleCancel();
            if (typeof onClose !== "undefined") {
              onClose();
            }
            if (typeof onSelect !== "undefined") {
              onSelect(channel);
            }
          }}
        />
      </Modal>
    </>
  );
};

export default ChannelTableModalWidget;
