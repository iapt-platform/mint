import Share from "./Share";
import { useIntl } from "react-intl";
import type { EResType } from "./utils";
import { useState } from "react";
import { Modal } from "antd";

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
  const [innerOpen, setInnerOpen] = useState(false);
  const intl = useIntl();

  const isModalOpen = open ?? innerOpen;

  const showModal = () => {
    setInnerOpen(true);
  };

  const handleOk = () => {
    if (onClose) {
      onClose();
    } else {
      setInnerOpen(false);
    }
  };

  const handleCancel = () => {
    handleOk();
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        destroyOnHidden={true}
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
