import { Modal, Upload, type UploadProps, message } from "antd";
import { useEffect, useState } from "react";
import { InboxOutlined, ExclamationCircleOutlined } from "@ant-design/icons";

import { get as getToken } from "../../reducers/current-user";
import type { UploadFile } from "antd/es/upload/interface";

import modal from "antd/lib/modal";
import { deleteRes, type IAttachmentResponse } from "../../api/Attachments";

const { Dragger } = Upload;

interface IWidget {
  replaceId?: string;
  open?: boolean;
  onOpenChange?: (ok: boolean) => void;
}
const AttachmentImportWidget = ({
  replaceId,
  open = false,
  onOpenChange,
}: IWidget) => {
  const [isOpen, setIsOpen] = useState(open);

  useEffect(() => {
    setIsOpen(open);
  }, [open]);

  const props: UploadProps = {
    name: "file",
    listType: "picture",
    multiple: replaceId ? false : true,
    action: `${import.meta.env.BASE_URL}/api/v2/attachment?id=${replaceId}`,
    headers: {
      Authorization: `Bearer ${getToken()}`,
    },
    onChange(info) {
      const { status } = info.file;
      if (status !== "uploading") {
        console.log(info.file, info.fileList);
      }
      if (status === "done") {
        message.success(`${info.file.name} file uploaded successfully.`);
      } else if (status === "error") {
        message.error(`${info.file.name} file upload failed.`);
      }
    },
    onDrop(e) {
      console.log("Dropped files", e.dataTransfer.files);
    },
    onRemove: (file: UploadFile<IAttachmentResponse>): boolean => {
      console.log("remove", file);
      if (file.response) {
        deleteRes(file.response.data.id);
        return true;
      } else {
        return false;
      }
    },
  };

  const handleOk = () => {
    setIsOpen(false);
    if (typeof onOpenChange !== "undefined") {
      onOpenChange(false);
    }
  };

  const handleCancel = () => {
    modal.confirm({
      title: "关闭上传窗口",
      icon: <ExclamationCircleOutlined />,
      content: "所有正在上传文件将取消上传。",
      okText: "确认",
      cancelText: "取消",
      onOk: () => {
        setIsOpen(false);
        if (typeof onOpenChange !== "undefined") {
          onOpenChange(false);
        }
      },
    });
  };

  return (
    <Modal
      destroyOnHidden={true}
      width={700}
      title="Upload"
      footer={false}
      maskClosable={false}
      open={isOpen}
      onOk={handleOk}
      onCancel={handleCancel}
    >
      <Dragger {...props}>
        <p className="ant-upload-drag-icon">
          <InboxOutlined />
        </p>
        <p className="ant-upload-text">
          Click or drag file to this area to upload
        </p>
        <p className="ant-upload-hint">
          Support for a single {replaceId ? "" : "or bulk"} upload. Strictly
          prohibit from uploading company data or other band files
        </p>
      </Dragger>
    </Modal>
  );
};

export default AttachmentImportWidget;
