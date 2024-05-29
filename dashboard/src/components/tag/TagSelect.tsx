import { useState } from "react";
import { Modal } from "antd";
import TagList from "./TagList";
import { ITagData } from "../api/Tag";

interface IWidget {
  studioName?: string;
  trigger?: React.ReactNode;
  onSelect?: Function;
}
const TagSelectWidget = ({ studioName, trigger, onSelect }: IWidget) => {
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
        width={500}
        title="标签列表"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <TagList
          readonly
          studioName={studioName}
          onSelect={(tag: ITagData) => {
            if (typeof onSelect !== "undefined") {
              onSelect(tag);
            }
          }}
        />
      </Modal>
    </>
  );
};

export default TagSelectWidget;
