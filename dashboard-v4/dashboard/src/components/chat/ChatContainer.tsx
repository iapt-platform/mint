import React, { useEffect, useState } from "react";
import { useChatData } from "../../hooks/useChatData";
import { SessionGroup } from "./SessionGroup";
import { ChatInput } from "./ChatInput";
import { StreamingMessage } from "./StreamingMessage";
import { mockChatState } from "../../hooks/mockChatData";

import "./style.css";

interface ChatContainerProps {
  chatId: string;
}

export function ChatContainer({ chatId }: ChatContainerProps) {
  const { chatState, actions } = useChatData(chatId);
  const [chatStateMock] = useState(mockChatState);
  useEffect(() => {
    actions.loadMessages();
  }, [chatId, actions.loadMessages, actions]);

  return (
    <div className="chat-container">
      <div className="messages-area">
        {chatStateMock.session_groups.map((session) => (
          <SessionGroup
            key={session.session_id}
            session={session}
            onVersionSwitch={actions.switchVersion}
            onRefresh={actions.refreshResponse}
            onEdit={actions.editMessage}
            onRetry={actions.retryMessage}
            onLike={actions.likeMessage}
            onDislike={actions.dislikeMessage}
            onCopy={actions.copyMessage}
            onShare={actions.shareMessage}
          />
        ))}

        {/* 流式消息显示 */}
        {chatState.streaming_message && (
          <StreamingMessage
            content={chatState.streaming_message}
            sessionId={chatState.streaming_session_id}
          />
        )}

        {/* 错误提示 */}
        {chatState.error && (
          <div className="error-message">{chatState.error}</div>
        )}
      </div>

      <ChatInput
        onSend={(content) => actions.editMessage("new", content)}
        disabled={chatState.is_loading}
        placeholder="输入你的问题..."
      />
    </div>
  );
}
