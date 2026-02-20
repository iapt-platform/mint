import _React from "react";
import Marked from "../general/Marked";

interface StreamingMessageProps {
  content: string;
  sessionId?: string;
}

export function StreamingMessage({ content }: StreamingMessageProps) {
  return (
    <div className="streaming-message">
      <div className="message-header">
        <span className="role-label">Assistant</span>
        <span className="streaming-indicator">正在生成中...</span>
      </div>

      <div className="message-content">
        <div className="message-text">
          <Marked text={content} />
          <span className="cursor">|</span>
        </div>
      </div>
    </div>
  );
}
