import { useState } from "react";
import { Modal } from "antd";
import Share, { EResType } from "./Share";
import { useIntl } from "react-intl";

interface IWidget {
  resId: string;
  resType: EResType;
  trigger?: React.ReactNode;
}
const ShareModalWidget = ({ resId, resType, trigger }: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const intl = useIntl();
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
        title={intl.formatMessage({ id: "labels.collaboration" })}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        footer={undefined}
      >
        <Share resId={resId} resType={resType} />
      </Modal>
    </>
  );
};

export default ShareModalWidget;
