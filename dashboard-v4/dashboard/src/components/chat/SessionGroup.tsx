import React from "react";
import { Button } from "antd";

import UserMessage from "./UserMessage";
import { SessionGroupProps } from "../../types/chat";
import { VersionSwitcher } from "./VersionSwitcher";
import AssistantMessage from "./AssistantMessage";

export function SessionGroup({
  session,
  onVersionSwitch,
  onRefresh,
  onEdit,
  onRetry,
  onLike,
  onDislike,
  onCopy,
  onShare,
}: SessionGroupProps) {
  const hasFailed = session.messages.some((m) => m.save_status === "failed");
  const isPending = session.messages.some((m) => m.save_status === "pending");
  const hasMultipleVersions = session.versions.length > 1;

  return (
    <div
      className={`session-group ${isPending ? "pending" : ""} ${
        hasFailed ? "failed" : ""
      }`}
    >
      {/* 用户消息 */}
      {session.user_message && (
        <UserMessage
          session={session}
          onEdit={(content) => onEdit && onEdit(session.session_id, content)}
          onCopy={() => onCopy && onCopy(session.user_message!.uid)}
          onVersionSwitch={onVersionSwitch}
        />
      )}

      {/* AI回答区域 */}
      <div className="ai-response">
        {/* 失败重试提示 */}
        {hasFailed && onRetry && (
          <div className="retry-section">
            <span className="error-message">回答生成失败</span>
            <Button
              size="small"
              type="primary"
              onClick={() => onRetry(session.messages[0].temp_id!)}
            >
              重试
            </Button>
          </div>
        )}

        {/* AI消息内容 */}
        {!hasFailed && session.ai_messages.length > 0 && (
          <AssistantMessage
            session={session}
            onRefresh={() => onRefresh && onRefresh(session.session_id)}
            onEdit={(content) => onEdit && onEdit(session.session_id, content)}
            isPending={isPending}
            onLike={onLike}
            onDislike={onDislike}
            onCopy={onCopy}
            onShare={onShare}
            onVersionSwitch={onVersionSwitch}
          />
        )}
      </div>
    </div>
  );
}
