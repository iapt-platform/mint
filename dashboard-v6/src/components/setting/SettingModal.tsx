import { Modal, Tabs } from "antd";
import { useState } from "react";
import SettingArticle from "./SettingArticle";
import SettingAccount from "./SettingAccount";
interface IWidget {
  trigger?: React.ReactNode;
  open?: boolean;
  onClose?: (isOpen: boolean) => void;
}
const SettingModalWidget = ({ trigger, open, onClose }: IWidget) => {
  const [isInnerOpen, setIsInnerOpen] = useState(false);
  const isModalOpen = open ?? isInnerOpen;

  const showModal = () => {
    setIsInnerOpen(true);
  };

  const handleOk = () => {
    if (typeof onClose !== "undefined") {
      onClose(false);
    }
    setIsInnerOpen(false);
  };

  const handleCancel = () => {
    if (typeof onClose !== "undefined") {
      onClose(false);
    }
    setIsInnerOpen(false);
  };
  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={"80%"}
        title="Setting"
        footer={false}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <Tabs
          tabPlacement="start"
          items={[
            { label: "账户", key: "account", children: <SettingAccount /> }, // 务必填写 key
            { label: "编辑器", key: "editor", children: <SettingArticle /> },
          ]}
        />
      </Modal>
    </>
  );
};

export default SettingModalWidget;
