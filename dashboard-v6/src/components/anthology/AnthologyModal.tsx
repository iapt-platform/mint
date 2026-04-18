import { useState } from "react";
import { Modal } from "antd";
import AnthologyList from "./AnthologyList";

interface IWidget {
  studioName?: string;
  trigger?: React.ReactNode;
  open?: boolean;
  onClose?: (closed: boolean) => void;
  onSelect?: (selected: string) => void;
  onCancel?: () => void;
}
const AnthologyModalWidget = ({
  studioName,
  trigger,
  open,
  onClose,
  onSelect,
}: IWidget) => {
  const [innerOpen, setInnerOpen] = useState(false);

  const isModalOpen = open ?? innerOpen;

  const openModal = () => {
    if (open === undefined) {
      setInnerOpen(true);
    } else {
      onClose?.(true);
    }
  };

  const closeModal = () => {
    onClose?.(false);
    if (open === undefined) {
      setInnerOpen(false);
    }
  };

  return (
    <>
      <span role="button" tabIndex={0} onClick={openModal}>
        {trigger}
      </span>

      <Modal
        width="80%"
        title="加入文集"
        open={isModalOpen}
        onOk={closeModal}
        onCancel={closeModal}
      >
        <AnthologyList
          title="选择文集"
          studioName={studioName}
          showCreate={false}
          showOption={false}
          onTitleClick={(id) => {
            onSelect?.(id);
            closeModal();
          }}
        />
      </Modal>
    </>
  );
};

export default AnthologyModalWidget;
