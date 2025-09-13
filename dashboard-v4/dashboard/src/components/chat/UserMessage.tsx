import React, { useState } from "react";
import { Button, Input, Typography } from "antd";
import { EditOutlined, CopyOutlined } from "@ant-design/icons";
import { UserMessageProps } from "../../types/chat";
import { VersionSwitcher } from "./VersionSwitcher";

const { TextArea } = Input;
const { Text } = Typography;

const UserMessage = ({
  session,
  onEdit,
  onCopy,
  onVersionSwitch,
}: UserMessageProps) => {
  const message = session.user_message;

  const [isEditing, setIsEditing] = useState(false);
  const [editContent, setEditContent] = useState(message?.content || "");

  const handleEdit = () => {
    if (onEdit && editContent.trim()) {
      onEdit(editContent.trim());
      setIsEditing(false);
    }
  };

  const handleCancel = () => {
    setEditContent(session.user_message?.content || "");
    setIsEditing(false);
  };
  if (!message) {
    return <></>;
  }
  return (
    <div className="user-message">
      <div className="message-content" style={{ maxWidth: "90%" }}>
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
            <Text>{message.content}</Text>
            {message.save_status === "pending" && (
              <span className="status-indicator pending">发送中...</span>
            )}
            {message.save_status === "failed" && (
              <span className="status-indicator failed">发送失败</span>
            )}
          </div>
        )}
      </div>
      <div className="message-header">
        <span className="role-label"></span>
        <div className="message-actions" style={{ display: "flex" }}>
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
              {/* 版本切换器 */}
              {session.versions && session.versions.length > 1 && (
                <VersionSwitcher
                  versions={session.versions}
                  currentVersion={session.current_version}
                  onSwitch={(versionIndex) =>
                    onVersionSwitch &&
                    onVersionSwitch(session.versions[versionIndex].message_id)
                  }
                />
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default UserMessage;
