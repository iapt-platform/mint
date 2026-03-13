import { Modal, Tabs } from "antd";
import { useState } from "react";
import SettingAccount from "./SettingAccount";
import { useIntl } from "react-intl";
import SettingNissaya from "./SettingNissaya";
import SettingDict from "./SettingDict";
import SettingEditor from "./SettingEditor";
interface IWidget {
  trigger?: React.ReactNode;
  open?: boolean;
  onClose?: (isOpen: boolean) => void;
}
const SettingModal = ({ trigger, open, onClose }: IWidget) => {
  const [isInnerOpen, setIsInnerOpen] = useState(false);
  const intl = useIntl();

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
        style={{ top: 10, maxWidth: 600 }}
      >
        <Tabs
          tabPlacement="top"
          items={[
            { label: "账户", key: "account", children: <SettingAccount /> }, // 务必填写 key
            { label: "编辑器", key: "editor", children: <SettingEditor /> },
            { label: "Nissaya", key: "nissaya", children: <SettingNissaya /> },
            {
              label: intl.formatMessage({
                id: `columns.library.dict.title`,
              }),
              key: "dict",
              children: <SettingDict />,
            },
          ]}
        />
      </Modal>
    </>
  );
};

export default SettingModal;
