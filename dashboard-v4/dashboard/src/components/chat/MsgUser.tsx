import { useCallback, useState } from "react";
import { Message } from "./AiChat";
import Marked from "../general/Marked";
import TextArea from "antd/lib/input/TextArea";
import { Button, message, Space, Tooltip } from "antd";

import {
  CheckOutlined,
  CloseOutlined,
  CopyOutlined,
  EditOutlined,
} from "@ant-design/icons";

interface IWidget {
  msg?: Message;
  onChange?: (value: string) => void;
}

const MsgUser = ({ msg, onChange }: IWidget) => {
  const [editing, setEditing] = useState(false);
  const [content, setContent] = useState(msg?.content ?? "");

  const confirmEdit = useCallback((): void => {
    onChange && onChange(content);
  }, [content, onChange]);

  const cancelEdit = useCallback((): void => {
    setEditing(false);
  }, []);

  const handleEditKeyPress = useCallback(
    (e: React.KeyboardEvent<HTMLTextAreaElement>): void => {
      if (e.key === "Enter" && e.ctrlKey) {
        e.preventDefault();
        confirmEdit();
      } else if (e.key === "Escape") {
        cancelEdit();
      }
    },
    [cancelEdit, confirmEdit]
  );

  return (
    <div
      style={{
        display: "flex",
        justifyContent: "flex-end",
      }}
    >
      <div
        style={{
          maxWidth: "70%",
          minWidth: 400,
          backgroundColor: "rgba(255, 255, 255, 0.8)",
          color: "black",
          borderRadius: "8px",
          padding: "16px",
          border: "none",
          boxShadow: "0 1px 2px rgba(0, 0, 0, 0.03)",
          textAlign: "left",
        }}
      >
        {editing ? (
          <div style={{ width: "100%" }}>
            <TextArea
              value={content}
              onChange={(e) => setContent(e.target.value)}
              onKeyPress={handleEditKeyPress}
              autoSize={{ minRows: 2, maxRows: 8 }}
              style={{ marginBottom: "8px", width: "100%" }}
            />
            <Space size="small">
              <Button
                size="small"
                type="primary"
                icon={<CheckOutlined />}
                onClick={() => confirmEdit()}
              >
                确认
              </Button>
              <Button
                size="small"
                icon={<CloseOutlined />}
                onClick={cancelEdit}
              >
                取消
              </Button>
            </Space>
          </div>
        ) : (
          <div>
            <div>
              <Marked text={msg?.content} />
            </div>
            <div
              style={{
                fontSize: "12px",
                opacity: 0.6,
                marginTop: "8px",
              }}
            >
              {msg?.timestamp}
            </div>
            <div>
              <Space size="small">
                <Tooltip title="复制">
                  <Button
                    size="small"
                    type="text"
                    icon={<CopyOutlined />}
                    onClick={() => {
                      msg &&
                        navigator.clipboard
                          .writeText(msg.content)
                          .then((value) => message.success("已复制到剪贴板"))
                          .catch((reason: any) => {
                            console.error("复制失败:", reason);
                            message.error("复制失败");
                          });
                    }}
                  />
                </Tooltip>
                <Tooltip title="复制">
                  <Button
                    size="small"
                    type="text"
                    icon={<EditOutlined />}
                    onClick={() => {
                      msg && setEditing(true);
                    }}
                  />
                </Tooltip>
              </Space>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default MsgUser;
