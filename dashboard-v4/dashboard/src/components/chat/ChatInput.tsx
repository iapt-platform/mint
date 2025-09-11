import React, { useState, useCallback } from "react";
import { Button, Input, Space } from "antd";
import { SendOutlined, PaperClipOutlined } from "@ant-design/icons";
import { ChatInputProps } from "../../types/chat";

const { TextArea } = Input;

export function ChatInput({ onSend, disabled, placeholder }: ChatInputProps) {
  const [inputValue, setInputValue] = useState("");

  const handleSend = useCallback(() => {
    if (!inputValue.trim() || disabled) return;

    onSend(inputValue.trim());
    setInputValue("");
  }, [inputValue, disabled, onSend]);

  const handleKeyPress = useCallback(
    (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        handleSend();
      }
    },
    [handleSend]
  );

  return (
    <div className="chat-input">
      <div className="input-area">
        <TextArea
          value={inputValue}
          onChange={(e) => setInputValue(e.target.value)}
          onKeyPress={handleKeyPress}
          placeholder={placeholder || "输入你的问题..."}
          autoSize={{ minRows: 1, maxRows: 6 }}
          disabled={disabled}
        />

        <div className="input-actions">
          <Space>
            <Button
              size="small"
              type="text"
              icon={<PaperClipOutlined />}
              disabled={disabled}
            />
            <Button
              type="primary"
              icon={<SendOutlined />}
              onClick={handleSend}
              disabled={!inputValue.trim() || disabled}
            />
          </Space>
        </div>
      </div>
    </div>
  );
}
