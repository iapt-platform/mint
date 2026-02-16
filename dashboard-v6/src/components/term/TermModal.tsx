import { useEffect, useState } from "react";
import { Modal, Space } from "antd";
import TermEdit from "./TermEdit";
import { ITermDataResponse } from "../api/Term";
import { Link } from "react-router-dom";

interface IWidget {
  trigger?: React.ReactNode;
  open?: boolean;
  id?: string;
  word?: string;
  tags?: string[];
  studioName?: string;
  channelId?: string;
  parentChannelId?: string;
  parentStudioId?: string;
  community?: boolean;
  onUpdate?: Function;
  onClose?: Function;
}
const TermModalWidget = ({
  trigger,
  open = false,
  id,
  word,
  tags,
  studioName,
  channelId,
  parentChannelId,
  parentStudioId,
  community = false,
  onUpdate,
  onClose,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);

  useEffect(() => {
    if (open) {
      showModal();
    } else {
      modalClose();
    }
  }, [open]);

  const modalClose = () => {
    setIsModalOpen(false);
    if (document.getElementsByTagName("body")[0].hasAttribute("style")) {
      document.getElementsByTagName("body")[0].removeAttribute("style");
    }
  };
  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    modalClose();
    if (typeof onClose !== "undefined") {
      onClose();
    }
  };

  const handleCancel = () => {
    modalClose();
    if (typeof onClose !== "undefined") {
      onClose();
    }
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        style={{ top: 20 }}
        width={760}
        title={
          <Space>
            {"术语"}
            <Link
              to={`/studio/${studioName}/term/list`}
              target="_blank"
              style={{ display: "none" }}
            >
              在studio中打开
            </Link>
          </Space>
        }
        footer={false}
        destroyOnClose={true}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <TermEdit
          id={id}
          word={word}
          tags={tags}
          studioName={studioName}
          channelId={channelId}
          parentChannelId={parentChannelId}
          parentStudioId={parentStudioId}
          community={community}
          onUpdate={(value: ITermDataResponse) => {
            modalClose();
            if (typeof onUpdate !== "undefined") {
              onUpdate(value);
            }
          }}
        />
      </Modal>
    </>
  );
};

export default TermModalWidget;
