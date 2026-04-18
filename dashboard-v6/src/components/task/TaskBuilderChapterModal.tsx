import { Modal } from "antd";

interface IModal {
  studioName?: string;
  channels?: string[];
  book?: number;
  para?: number;
  open?: boolean;
  onClose?: () => void;
}
export const TaskBuilderChapterModal = ({ open = false, onClose }: IModal) => {
  return (
    <>
      <Modal
        destroyOnHidden={true}
        mask={{ closable: false }}
        width={1400}
        style={{ top: 10 }}
        title={""}
        footer={false}
        open={open}
        onOk={onClose}
        onCancel={onClose}
      >
        mock
      </Modal>
    </>
  );
};
