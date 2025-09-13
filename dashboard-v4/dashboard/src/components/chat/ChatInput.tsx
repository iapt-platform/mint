import React, { useState, useCallback, useEffect } from "react";
import {
  Affix,
  Button,
  Card,
  Dropdown,
  Input,
  MenuProps,
  Space,
  Tooltip,
} from "antd";
import {
  SendOutlined,
  PaperClipOutlined,
  DownOutlined,
} from "@ant-design/icons";
import { ChatInputProps } from "../../types/chat";
import PromptButtonGroup from "./PromptButtonGroup";
import { IAiModel } from "../api/ai";
import { useAppSelector } from "../../hooks";
import { siteInfo } from "../../reducers/layout";

const { TextArea } = Input;

export function ChatInput({
  onSend,
  onModelChange,
  disabled,
  placeholder,
}: ChatInputProps) {
  const [inputValue, setInputValue] = useState("");
  const [selectedModel, setSelectedModel] = useState<string>("");
  const [models, setModels] = useState<IAiModel[]>();
  const site = useAppSelector(siteInfo);

  useEffect(() => {
    const allModels = site?.settings?.models?.chat ?? [];
    setModels(allModels);
    if (
      site?.settings?.models?.chat &&
      site?.settings?.models?.chat.length > 0
    ) {
      const modelId = site?.settings?.models?.chat[0].uid;
      setSelectedModel(modelId);
      onModelChange && onModelChange(allModels?.find((m) => m.uid === modelId));
    }
  }, [onModelChange, site?.settings?.models?.chat]);

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

  const modelMenu: MenuProps = {
    selectedKeys: [selectedModel],
    onClick: ({ key }) => {
      console.log("setSelectedModel", key);
      setSelectedModel(key);
      onModelChange && onModelChange(models?.find((m) => m.uid === key));
    },
    items: models?.map((model) => ({
      key: model.uid,
      label: model.name,
    })),
  };
  /**
 *     <div className="chat-input">
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
 */
  return (
    <Affix offsetBottom={10}>
      <Card style={{ borderRadius: "10px", borderColor: "#d9d9d9" }}>
        <div style={{ maxWidth: "1200px", margin: "0 auto" }}>
          <div style={{ display: "flex", marginBottom: "8px" }}>
            <TextArea
              value={inputValue}
              onChange={(e) => setInputValue(e.target.value)}
              onKeyPress={handleKeyPress}
              placeholder={placeholder || "提出你的问题，如：总结下面的内容..."}
              autoSize={{ minRows: 1, maxRows: 6 }}
              style={{ resize: "none", paddingRight: "48px" }}
            />
          </div>

          <div
            style={{
              display: "flex",
              justifyContent: "space-between",
              alignItems: "center",
            }}
          >
            <Space>
              <Tooltip title="附加文件">
                <Button size="small" type="text" icon={<PaperClipOutlined />} />
              </Tooltip>
              <PromptButtonGroup onText={setInputValue} />
            </Space>
            <Space>
              <Dropdown menu={modelMenu} trigger={["click"]}>
                <Button size="small" type="text">
                  {models?.find((m) => m.uid === selectedModel)?.name}
                  <DownOutlined />
                </Button>
              </Dropdown>
              <Button
                type="primary"
                icon={<SendOutlined />}
                onClick={handleSend}
                disabled={!inputValue.trim() || disabled}
              />
            </Space>
          </div>
        </div>
      </Card>
    </Affix>
  );
}
