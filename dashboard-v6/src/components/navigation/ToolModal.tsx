import { Button, Modal, Space, Tooltip, Typography } from "antd";
import { CloseOutlined, ExportOutlined } from "@ant-design/icons";
import type { ReactNode } from "react";
import { useIntl } from "react-intl";
import { fullUrl } from "../../utils";

interface IWidget {
  open: boolean;
  title: ReactNode;
  /** 「在新标签页打开」的目标路径（站内路径，如 /dictionary/dhamma） */
  url: string;
  onClose: () => void;
  children?: ReactNode;
}

/**
 * 主导航「工具」各子项打开的弹窗：标题 + 右上角「在新标签页打开」「关闭」。
 *
 * 关闭后不销毁，再打开时状态还在（与搜索弹窗一致）。
 * 弹窗里的链接一律新标签页打开，由各内容自己处理。
 */
const ToolModal = ({ open, title, url, onClose, children }: IWidget) => {
  const intl = useIntl();

  return (
    <Modal
      open={open}
      onCancel={onClose}
      footer={null}
      closable={false}
      width="min(1160px, calc(100vw - 32px))"
      style={{ top: "5vh", paddingBottom: 0 }}
      title={
        <div
          style={{
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between",
            gap: 8,
          }}
        >
          <Typography.Text strong ellipsis>
            {title}
          </Typography.Text>
          <Space size={4}>
            <Tooltip title={intl.formatMessage({ id: "search.open_full_page" })}>
              <Button
                type="text"
                icon={<ExportOutlined />}
                aria-label={intl.formatMessage({
                  id: "search.open_full_page",
                })}
                onClick={() => window.open(fullUrl(url), "_blank")}
              />
            </Tooltip>
            <Button
              type="text"
              icon={<CloseOutlined />}
              onClick={onClose}
              aria-label="close"
            />
          </Space>
        </div>
      }
      styles={{
        container: {
          height: "90vh",
          display: "flex",
          flexDirection: "column",
          overflow: "hidden",
        },
        body: { flex: 1, minHeight: 0, overflowY: "auto" },
      }}
    >
      {children}
    </Modal>
  );
};

export default ToolModal;
