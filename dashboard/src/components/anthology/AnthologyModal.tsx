import { useState } from "react";
import { Modal } from "antd";
import AnthologyList from "./AnthologyList";

interface IWidget {
  studioName?: string;
  trigger?: React.ReactNode;
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

export default Widget;
