import { useEffect, useState } from "react";
import { Modal } from "antd";
import Share, { EResType } from "./Share";
import { useIntl } from "react-intl";

interface IWidget {
  resId: string;
  resType: EResType;
  trigger?: React.ReactNode;
  open?: boolean;
  onClose?: () => void;
}
const ShareModalWidget = ({
  resId,
  resType,
  trigger,
  open,
  onClose,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);
  const intl = useIntl();

  useEffect(() => setIsModalOpen(open), [open]);
  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    if (onClose) {
      onClose();
    } else {
      setIsModalOpen(false);
    }
  };

  const handleCancel = () => {
    handleOk();
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        destroyOnClose={true}
        width={700}
        title={intl.formatMessage({ id: "labels.collaboration" })}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        footer={false}
      >
        <Share resId={resId} resType={resType} />
      </Modal>
    </>
  );
};

export default ShareModalWidget;
