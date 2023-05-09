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
        title="Basic Modal"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        width={1000}
      >
        <Video src={src} type={type} />
        <div>
          src = {src}
          type = {type}
        </div>
      </Modal>
    </>
  );
};

export default VideoModalWidget;
