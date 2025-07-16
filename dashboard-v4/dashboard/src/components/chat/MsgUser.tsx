import { useCallback, useState } from "react";
import { Message } from "./AiChat";
import Marked from "../general/Marked";
import TextArea from "antd/lib/input/TextArea";
import { Button, Space } from "antd";

import { CheckOutlined, CloseOutlined } from "@ant-design/icons";

interface IWidget {
  msg?: Message;
  onChange?: (value: string) => void;
}

const MsgUser = ({ msg, onChange }: IWidget) => {
  const [editing, setEditing] = useState(false);
  const [content, setContent] = useState("");

  const confirmEdit = useCallback((): void => {
    onChange && onChange(content);
    setContent("");
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

  return editing ? (
    <div>
      <TextArea
        value={content}
        onChange={(e) => setContent(e.target.value)}
        onKeyPress={handleEditKeyPress}
        autoSize={{ minRows: 2, maxRows: 8 }}
        style={{ marginBottom: "8px" }}
      />
      <Space size="small">
        <Button
          size="small"
          type="primary"
          icon={<CheckOutlined />}
          onClick={confirmEdit}
        >
          确认
        </Button>
        <Button size="small" icon={<CloseOutlined />} onClick={cancelEdit}>
          取消
        </Button>
      </Space>
    </div>
  ) : (
    <div>
      <div
        style={{
          fontSize: "14px",
          lineHeight: "1.5",
          whiteSpace: "pre-wrap",
          wordBreak: "break-word",
        }}
      >
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
    </div>
  );
};

export default MsgUser;
