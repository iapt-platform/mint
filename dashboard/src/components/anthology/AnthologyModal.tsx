import { useState } from "react";
import { Modal } from "antd";
import AnthologyList from "./AnthologyList";

interface IWidget {
  studioName?: string;
  trigger?: JSX.Element;
  onSelect?: Function;
  onCancel?: Function;
}
const Widget = ({ studioName, trigger, onSelect, onCancel }: IWidget) => {
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
        width={"80%"}
        title="选择文集"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <AnthologyList
          studioName={studioName}
          showCreate={false}
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

export default Widget;
