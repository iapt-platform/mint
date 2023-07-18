import { useState } from "react";
import { Modal } from "antd";
import TermEdit from "./TermEdit";
import { ITermDataResponse } from "../api/Term";

interface IWidget {
  trigger?: React.ReactNode;
  id?: string;
  word?: string;
  studioName?: string;
  channelId?: string;
  parentChannelId?: string;
  parentStudioId?: string;
  onUpdate?: Function;
  onClose?: Function;
}
const TermModalWidget = ({
  trigger,
  id,
  word,
  studioName,
  channelId,
  parentChannelId,
  parentStudioId,
  onUpdate,
  onClose,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(false);

  const modalClone = () => {
    setIsModalOpen(false);
    if (document.getElementsByTagName("body")[0].hasAttribute("style")) {
      document.getElementsByTagName("body")[0].removeAttribute("style");
    }
  };
  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    modalClone();
    if (typeof onClose !== "undefined") {
      onClose();
    }
  };

  const handleCancel = () => {
    modalClone();
    if (typeof onClose !== "undefined") {
      onClose();
    }
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={760}
        title="术语"
        footer={false}
        destroyOnClose={true}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <TermEdit
          id={id}
          word={word}
          studioName={studioName}
          channelId={channelId}
          parentChannelId={parentChannelId}
          parentStudioId={parentStudioId}
          onUpdate={(value: ITermDataResponse) => {
            setIsModalOpen(false);
            if (typeof onUpdate !== "undefined") {
              onUpdate(value);
            }
          }}
        />
      </Modal>
    </>
  );
};

export default TermModalWidget;
