import React, { useState } from "react";
import { Button, Input } from "antd";
import { EditOutlined, CopyOutlined } from "@ant-design/icons";
import { UserMessageProps } from "../../types/chat";

const { TextArea } = Input;

const UserMessage = ({ message, onEdit, onCopy }: UserMessageProps) => {
  const [isEditing, setIsEditing] = useState(false);
  const [editContent, setEditContent] = useState(message.content || "");

  const handleEdit = () => {
    if (onEdit && editContent.trim()) {
      onEdit(editContent.trim());
      setIsEditing(false);
    }
  };

  const handleCancel = () => {
    setEditContent(message.content || "");
    setIsEditing(false);
  };

  return (
    <div className="user-message">
      <div className="message-header">
        <span className="role-label">You</span>
        <div className="message-actions">
          {!isEditing && (
            <>
              <Button
                size="small"
                type="text"
                icon={<EditOutlined />}
                onClick={() => setIsEditing(true)}
              />
              <Button
                size="small"
                type="text"
                icon={<CopyOutlined />}
                onClick={onCopy}
              />
            </>
          )}
        </div>
      </div>

      <div className="message-content">
        {isEditing ? (
          <div className="edit-area">
            <TextArea
              value={editContent}
              onChange={(e) => setEditContent(e.target.value)}
              autoSize={{ minRows: 2, maxRows: 8 }}
              autoFocus
            />
            <div className="edit-actions">
              <Button size="small" onClick={handleCancel}>
                取消
              </Button>
              <Button size="small" type="primary" onClick={handleEdit}>
                保存
              </Button>
            </div>
          </div>
        ) : (
          <div className="message-text">
            {message.content}
            {message.save_status === "pending" && (
              <span className="status-indicator pending">发送中...</span>
            )}
            {message.save_status === "failed" && (
              <span className="status-indicator failed">发送失败</span>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default UserMessage;
