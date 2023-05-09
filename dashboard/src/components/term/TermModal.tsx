import { useState } from "react";
import { Modal } from "antd";
import TermEdit from "./TermEdit";

interface IWidget {
  trigger?: React.ReactNode;
  id?: string;
  word?: string;
  studioName?: string;
  channelId?: string;
  onUpdate?: Function;
}
const TermModalWidget = ({
  trigger,
  id,
  word,
  studioName,
  channelId,
  onUpdate,
}: IWidget) => {
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
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={760}
        title="术语"
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
          onUpdate={() => {
            setIsModalOpen(false);
            if (typeof onUpdate !== "undefined") {
              onUpdate();
            }
          }}
        />
      </Modal>
    </>
  );
};

export default TermModalWidget;
