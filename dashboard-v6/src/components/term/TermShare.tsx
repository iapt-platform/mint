import { useIntl } from "react-intl";
import { useState } from "react";
import { Button, Divider, Input, Modal, Typography, message } from "antd";
import {
  CopyOutlined,
  ExportOutlined,
  GlobalOutlined,
  LinkOutlined,
} from "@ant-design/icons";

import { fullUrl } from "../../utils";

const { Text } = Typography;

interface IWidget {
  id?: string;
  trigger?: React.ReactNode;
  open?: boolean;
  onClose?: () => void;
}

const TermShareWidget = ({ id, trigger, open, onClose }: IWidget) => {
  const intl = useIntl();
  const [innerOpen, setInnerOpen] = useState(false);

  const isOpen = open ?? innerOpen;

  // 藏经阁（对外公开）访问链接
  const libraryUrl = id
    ? `${import.meta.env.VITE_APP_API_SERVER}/library/wiki/${intl.locale}/${id}`
    : "";
  // 译经楼（工作台）访问链接
  const studioUrl = id ? fullUrl(`/workspace/term/${id}`) : "";

  const close = () => {
    if (onClose) {
      onClose();
    } else {
      setInnerOpen(false);
    }
  };

  const copy = (url: string) => {
    navigator.clipboard
      .writeText(url)
      .then(() => {
        message.success(intl.formatMessage({ id: "message.copy.success" }));
      })
      .catch(() => {
        message.error(intl.formatMessage({ id: "message.copy.fail" }));
      });
  };

  const linkRow = (
    label: string,
    url: string,
    prefix: React.ReactNode
  ): React.ReactNode => (
    <div>
      <Text type="secondary">{label}</Text>
      <div style={{ display: "flex", gap: 8, marginTop: 8 }}>
        <Input
          readOnly
          value={url}
          prefix={prefix}
          style={{ flex: 1 }}
          onFocus={(e) => e.target.select()}
        />
        <Button icon={<CopyOutlined />} onClick={() => copy(url)}>
          {intl.formatMessage({ id: "buttons.copy" })}
        </Button>
        <Button
          icon={<ExportOutlined />}
          onClick={() => window.open(url, "_blank")}
        >
          {intl.formatMessage({ id: "buttons.open" })}
        </Button>
      </div>
    </div>
  );

  return (
    <>
      <span onClick={() => setInnerOpen(true)}>{trigger}</span>
      <Modal
        destroyOnHidden
        width={560}
        title={intl.formatMessage({ id: "buttons.share" })}
        open={isOpen}
        onCancel={close}
        footer={null}
      >
        {linkRow(
          intl.formatMessage({ id: "labels.library.access.link" }),
          libraryUrl,
          <GlobalOutlined />
        )}
        <Text
          type="secondary"
          style={{ fontSize: 12, display: "block", marginTop: 8 }}
        >
          {intl.formatMessage({ id: "labels.library.access.tip" })}
        </Text>
        <Divider style={{ margin: "16px 0" }} />
        {linkRow(
          intl.formatMessage({ id: "labels.studio.access.link" }),
          studioUrl,
          <LinkOutlined />
        )}
      </Modal>
    </>
  );
};

export default TermShareWidget;
