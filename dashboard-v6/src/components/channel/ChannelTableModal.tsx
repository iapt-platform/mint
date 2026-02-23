import React, { useEffect, useState } from "react";
import { Modal } from "antd";

import ChannelTable, { type IChapter } from "./ChannelTable";
import { useAppSelector } from "../../../src/hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";

import type { IChannel, TChannelType } from "../../api/Channel";
import { useIntl } from "react-intl";
import type { ArticleType } from "../../api/Corpus";

interface IWidget {
  trigger?: React.ReactNode;
  channelType?: TChannelType;
  type?: ArticleType | "editable";
  articleId?: string;
  multiSelect?: boolean;
  disableChannels?: string[];
  open?: boolean;
  chapter?: IChapter;
  onClose?: () => void;
  onSelect?: (channel: IChannel) => void;
}
const ChannelTableModalWidget = ({
  trigger,
  type,
  disableChannels,
  channelType,
  open = false,
  chapter,
  onClose,
  onSelect,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);
  const intl = useIntl();
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
        width={"90%"}
        title={intl.formatMessage({
          id: "buttons.select.channel",
        })}
        destroyOnClose
        footer={false}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <div style={{ overflowX: "scroll" }}>
          <ChannelTable
            studioName={user?.realName}
            type={type}
            chapter={chapter}
            channelType={channelType}
            disableChannels={disableChannels}
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
        </div>
      </Modal>
    </>
  );
};

export default ChannelTableModalWidget;
