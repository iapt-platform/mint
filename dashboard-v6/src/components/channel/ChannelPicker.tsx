import React, { useEffect, useState } from "react";
import { Modal } from "antd";

import ChannelPickerTable from "./ChannelPickerTable";

import { useIntl } from "react-intl";
import type { ArticleType } from "../../api/Corpus";
import type { IChannel } from "../../api/Channel";

interface IWidget {
  trigger?: React.ReactNode;
  type?: ArticleType | "editable";
  articleId?: string;
  multiSelect?: boolean;
  open?: boolean;
  defaultOwner?: string;
  onClose?: () => void;
  onSelect?: (channels: IChannel[]) => void;
}
const ChannelPickerWidget = ({
  trigger,
  type,
  articleId,
  multiSelect = true,
  open = false,
  defaultOwner,
  onClose,
  onSelect,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);
  const intl = useIntl();

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
        style={{ maxWidth: 600 }}
        title={intl.formatMessage({
          id: "buttons.select.channel",
        })}
        footer={false}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <ChannelPickerTable
          type={type}
          articleId={articleId}
          multiSelect={multiSelect}
          defaultOwner={defaultOwner}
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
