import { useState } from "react";
import { Modal } from "antd";
import Share, { EResType } from "./Share";

interface IWidget {
  resId: string;
  resType: EResType;
  trigger?: React.ReactNode;
}
const ShareModalWidget = ({ resId, resType, trigger }: IWidget) => {
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
        destroyOnClose={true}
        width={700}
        title="协作"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <Share resId={resId} resType={resType} />
      </Modal>
    </>
  );
};

export default ShareModalWidget;
