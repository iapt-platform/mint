import React from "react";
import { Button, Space } from "antd";
import {
  RedoOutlined,
  LikeOutlined,
  DislikeOutlined,
  CopyOutlined,
  ShareAltOutlined,
} from "@ant-design/icons";
import { AssistantMessageProps } from "../../types/chat";

const AssistantMessage = ({
  messages,
  onRefresh,
  onEdit,
  isPending,
  onLike,
  onDislike,
  onCopy,
  onShare,
}: AssistantMessageProps) => {
  const mainMessage = messages.find((m) => m.role === "assistant" && m.content);
  const toolMessages = messages.filter((m) => m.role === "tool");

  const handleCopy = () => {
    if (mainMessage?.content && onCopy) {
      onCopy(mainMessage.uid);
    }
  };

  const handleShare = async () => {
    if (mainMessage && onShare) {
      try {
        const shareUrl = await onShare(mainMessage.uid);
        // 可以显示分享链接或复制到剪贴板
        navigator.clipboard.writeText(shareUrl);
      } catch (err) {
        console.error("分享失败:", err);
      }
    }
  };

  return (
    <div className="assistant-message">
      <div className="message-header">
        <span className="role-label">Assistant</span>
        {mainMessage?.model_id && (
          <span className="model-info">{mainMessage.model_id}</span>
        )}

        {!isPending && (
          <div className="message-actions">
            <Space size="small">
              <Button
                size="small"
                type="text"
                icon={<RedoOutlined />}
                onClick={onRefresh}
              />
              <Button
                size="small"
                type="text"
                icon={<LikeOutlined />}
                onClick={() => mainMessage && onLike && onLike(mainMessage.uid)}
              />
              <Button
                size="small"
                type="text"
                icon={<DislikeOutlined />}
                onClick={() =>
                  mainMessage && onDislike && onDislike(mainMessage.uid)
                }
              />
              <Button
                size="small"
                type="text"
                icon={<CopyOutlined />}
                onClick={handleCopy}
              />
              <Button
                size="small"
                type="text"
                icon={<ShareAltOutlined />}
                onClick={handleShare}
              />
            </Space>
          </div>
        )}
      </div>

      <div className="message-content">
        {/* Tool calls 显示 */}
        {toolMessages.length > 0 && (
          <div className="tool-calls">
            {toolMessages.map((toolMsg, index) => (
              <div key={toolMsg.uid} className="tool-result">
                <span className="tool-label">Tool {index + 1}</span>
                <div className="tool-content">{toolMsg.content}</div>
              </div>
            ))}
          </div>
        )}

        {/* 主要回答内容 */}
        {mainMessage?.content && (
          <div className="message-text">
            {mainMessage.content}
            {isPending && (
              <span className="status-indicator pending">生成中...</span>
            )}
          </div>
        )}

        {/* Token 使用信息 */}
        {mainMessage?.metadata?.token_usage && (
          <div className="token-info">
            Token: {mainMessage.metadata.token_usage.total_tokens}
          </div>
        )}
      </div>
    </div>
  );
};

export default AssistantMessage;
