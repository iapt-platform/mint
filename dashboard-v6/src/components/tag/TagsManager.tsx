import { useState } from "react";
import { Alert, Modal } from "antd";

import TagsOnItem from "./TagsOnItem";

interface IWidget {
  studioName?: string;
  courseId?: string;
  resId?: string;
  resType?: string;
  title?: React.ReactNode;
  trigger?: React.ReactNode;
}
const TagsManagerWidget = ({
  studioName,
  courseId,
  resId,
  resType,
  title,
  trigger,
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
        destroyOnHidden
        footer={false}
      >
        {title ? <Alert title={title} /> : undefined}
        <TagsOnItem
          studioName={studioName}
          courseId={courseId}
          resId={resId}
          resType={resType}
        />
      </Modal>
    </>
  );
};

export default TagsManagerWidget;
