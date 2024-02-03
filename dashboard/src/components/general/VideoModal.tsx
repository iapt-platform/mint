import { useState } from "react";
import { Modal } from "antd";
import Video from "./Video";

interface IWidget {
  src?: string;
  type?: string;
  trigger?: JSX.Element;
}
export const VideoModalWidget = ({ src, type, trigger }: IWidget) => {
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
        style={{ top: 20 }}
        title="Basic Modal"
        footer={false}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        width={800}
        destroyOnClose
        maskClosable={false}
        mask={false}
      >
        <div>
          <Video src={src} type={type} />
        </div>
      </Modal>
    </>
  );
};

export default VideoModalWidget;
