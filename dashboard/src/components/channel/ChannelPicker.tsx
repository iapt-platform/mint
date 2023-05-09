import { useState } from "react";
import { Button, Modal } from "antd";

import ChannelPickerTable from "./ChannelPickerTable";
import { IChannel } from "./Channel";
import { ArticleType } from "../article/Article";

interface IWidget {
  type?: ArticleType | "editable";
  articleId?: string;
  multiSelect?: boolean;
}
const ChannelPickerWidget = ({ type, articleId, multiSelect }: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(false);

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
      <Button type="primary" onClick={showModal}>
        Select channel
      </Button>
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
          multiSelect={multiSelect}
          onSelect={(e: IChannel) => {
            console.log(e);
            handleCancel();
          }}
        />
      </Modal>
    </>
  );
};

export default ChannelPickerWidget;
