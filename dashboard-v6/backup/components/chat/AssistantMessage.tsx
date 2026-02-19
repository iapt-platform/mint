import _React from "react";
import { Button, Space } from "antd";
import {
  RedoOutlined,
  LikeOutlined,
  DislikeOutlined,
  CopyOutlined,
  ShareAltOutlined,
} from "@ant-design/icons";
import type { AssistantMessageProps } from "../../types/chat"
import Marked from "../general/Marked";
import { VersionSwitcher } from "./VersionSwitcher";
import ToolMessage from "./ToolMessage";

const AssistantMessage = ({
  session,
  onRefresh,
  ___onEdit,
  isPending,
  onLike,
  onDislike,
  onCopy,
  onShare,
  onVersionSwitch,
}: AssistantMessageProps) => {
  const messages = session.ai_messages;

  const mainMessage = messages.find((m) => m.role === "assistant" && m.content);
  const _____toolMessages = messages.filter((m) => m.role === "tool");

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
        <span className="role-label">
          {mainMessage?.model_id && (
            <span className="model-info">{mainMessage.model_id}</span>
          )}
        </span>
      </div>

      <div className="message-content" style={{ backgroundColor: "unset" }}>
        {/* Tool calls 显示 */}
        <ToolMessage session={session} />
        {/* 主要回答内容 */}
        {mainMessage?.content && (
          <div className="message-text">
            <Marked text={mainMessage.content} />
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
      {!isPending && (
        <div className="message-actions">
          <Space size="small">
            <Button
              size="small"
              type="text"
              icon={<CopyOutlined />}
              onClick={handleCopy}
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
              icon={<ShareAltOutlined />}
              onClick={handleShare}
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
            <Button
              size="small"
              type="text"
              icon={<RedoOutlined />}
              onClick={onRefresh}
            />
          </Space>
        </div>
      )}
    </div>
  );
};

export default AssistantMessage;
