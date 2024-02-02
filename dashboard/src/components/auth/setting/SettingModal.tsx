import { Modal, Tabs } from "antd";
import { useEffect, useState } from "react";
import SettingArticle from "./SettingArticle";
import SettingAccount from "./SettingAccount";
interface IWidget {
  trigger?: React.ReactNode;
  open?: boolean;
  onClose?: Function;
}
const SettingModalWidget = ({ trigger, open, onClose }: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);

  useEffect(() => setIsModalOpen(open), [open]);
  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    if (typeof onClose !== "undefined") {
      onClose(false);
    }
    setIsModalOpen(false);
  };

  const handleCancel = () => {
    if (typeof onClose !== "undefined") {
      onClose(false);
    }
    setIsModalOpen(false);
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
          tabPosition="left"
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
