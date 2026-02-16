import { useEffect, useState } from "react";
import { Modal } from "antd";
import AnthologyList from "./AnthologyList";

interface IWidget {
  studioName?: string;
  trigger?: React.ReactNode;
  open?: boolean;
  onClose?: Function;
  onSelect?: Function;
  onCancel?: Function;
}
const AnthologyModalWidget = ({
  studioName,
  trigger,
  open = false,
  onClose,
  onSelect,
  onCancel,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);

  useEffect(() => setIsModalOpen(open), [open]);
  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    if (typeof onClose !== "undefined") {
      onClose(false);
    }
    setIsModalOpen(false);
  };

  const handleCancel = () => {
    if (typeof onClose !== "undefined") {
      onClose(false);
    }
    setIsModalOpen(false);
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={"80%"}
        title="加入文集"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <AnthologyList
          title={"选择文集"}
          studioName={studioName}
          showCreate={false}
          showOption={false}
          onTitleClick={(id: string) => {
            if (typeof onSelect !== "undefined") {
              onSelect(id);
            }
          }}
        />
      </Modal>
    </>
  );
};

export default AnthologyModalWidget;
