import { useEffect, useState } from "react";
import { Modal } from "antd";
import Video from "./Video";

interface IWidget {
  src?: string;
  type?: string;
  open?: boolean;
  trigger?: JSX.Element;
  onOpenChange?: Function;
}
export const VideoModalWidget = ({
  src,
  type,
  open = false,
  trigger,
  onOpenChange,
}: IWidget) => {
  //TODO 跟video ctl 合并
  const [isModalOpen, setIsModalOpen] = useState(open);

  useEffect(() => setIsModalOpen(open), [open]);

  const showModal = () => {
    setIsModalOpen(true);
    if (typeof onOpenChange !== "undefined") {
      onOpenChange(true);
    }
  };

  const handleClose = () => {
    setIsModalOpen(false);
    if (typeof onOpenChange !== "undefined") {
      onOpenChange(false);
    }
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        style={{ top: 20 }}
        title="Basic Modal"
        footer={false}
        open={isModalOpen}
        onOk={handleClose}
        onCancel={handleClose}
        width={800}
        destroyOnClose={false}
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
