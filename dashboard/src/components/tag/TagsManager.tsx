import { useState } from "react";
import { Alert, Modal } from "antd";

import TagsOnItem from "./TagsOnItem";

interface IWidget {
  studioName?: string;
  resId?: string;
  resType?: string;
  title?: React.ReactNode;
  trigger?: React.ReactNode;
  onSelect?: Function;
}
const TagsManagerWidget = ({
  studioName,
  resId,
  resType,
  title,
  trigger,
  onSelect,
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
        width={500}
        title={`${studioName}标签列表`}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        destroyOnClose
        footer={false}
      >
        {title ? <Alert message={title} /> : undefined}
        <TagsOnItem studioName={studioName} resId={resId} resType={resType} />
      </Modal>
    </>
  );
};

export default TagsManagerWidget;
