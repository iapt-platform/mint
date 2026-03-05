import React, { useState } from "react";
import { Modal } from "antd";
import RecentList, { type IRecent } from "./RecentList";
import { useIntl } from "react-intl";

interface IWidget {
  trigger?: React.ReactNode;
  open?: boolean;
  onSelect?: (
    event: React.MouseEvent<HTMLElement, MouseEvent>,
    row: IRecent
  ) => void;
  onOpenChange?: (open: boolean) => void;
}
const RecentModal = ({
  trigger,
  open = false,
  onSelect,
  onOpenChange,
}: IWidget) => {
  const [innerOpen, setInnerOpen] = useState<boolean>();
  const intl = useIntl();

  const isModalOpen = open !== undefined ? open : innerOpen;

  const showModal = () => {
    setInnerOpen(true);
    onOpenChange?.(true);
  };

  const handleOk = () => {
    setInnerOpen(false);
    onOpenChange?.(false);
  };

  const handleCancel = () => {
    setInnerOpen(false);
    onOpenChange?.(false);
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={"80%"}
        title={intl.formatMessage({
          id: `labels.recent-scan`,
        })}
        footer={false}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        destroyOnHidden
      >
        <RecentList onSelect={onSelect} />
      </Modal>
    </>
  );
};

export default RecentModal;
