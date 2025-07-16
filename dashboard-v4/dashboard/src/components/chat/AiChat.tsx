import React, { useState, useRef, useEffect, useCallback } from "react";
import {
  Input,
  Button,
  Avatar,
  Dropdown,
  message,
  Tooltip,
  Space,
  Spin,
  MenuProps,
  Card,
  Affix,
  Typography,
} from "antd";
import {
  SendOutlined,
  CopyOutlined,
  EditOutlined,
  ReloadOutlined,
  DownOutlined,
  UserOutlined,
  RobotOutlined,
  PaperClipOutlined,
  LeftOutlined,
  RightOutlined,
  CheckOutlined,
  CloseOutlined,
} from "@ant-design/icons";
import Marked from "../general/Marked";
import { IAiModel, IAiModelListResponse } from "../api/ai";
import { get } from "../../request";
import User from "../auth/User";

const { TextArea } = Input;
const { Text } = Typography;

// 类型定义
export interface MessageVersion {
  content: string;
  model: string;
}

export interface Message {
  id: number;
  type: "user" | "ai" | "error";
  content: string;
  timestamp: string;
  model?: string;
  versions?: MessageVersion[];
  currentVersionIndex?: number;
}

interface OpenAIMessage {
  role: "system" | "user" | "assistant";
  content: string;
}

interface StreamTypeController {
  addToken: (token: string) => void;
  complete: () => void;
}

interface OpenAIStreamResponse {
  choices?: Array<{
    delta?: {
      content?: string;
    };
  }>;
}

interface IWidget {
  initMessage?: string;
  systemPrompt?: string;
  onChat?: () => void;
}

const AIChatComponent = ({
  initMessage,
  systemPrompt = "你是一个巴利语专家",
  onChat,
}: IWidget) => {
  const [messages, setMessages] = useState<Message[]>([]);
  const [inputValue, setInputValue] = useState<string>("");
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [selectedModel, setSelectedModel] = useState<string>("");
  const [editingMessageId, setEditingMessageId] = useState<number | null>(null);
  const [editingContent, setEditingContent] = useState<string>("");
  const [refreshingMessageId, setRefreshingMessageId] = useState<number | null>(
    null
  );

  const messagesEndRef = useRef<HTMLDivElement>(null);
  const [isTyping, setIsTyping] = useState<boolean>(false);
  const [currentTypingMessage, setCurrentTypingMessage] = useState<string>("");
  const [models, setModels] = useState<IAiModel[]>();

  const scrollToBottom = useCallback(() => {
    messagesEndRef.current?.scrollIntoView({
      behavior: "smooth",
      block: "center",
    });
  }, []);

  useEffect(() => {
    const url = `/v2/ai-model?view=chat`;
    console.info("api request", url);
    get<IAiModelListResponse>(url).then((json) => {
      if (json.ok) {
        setModels(json.data.rows);
        if (json.data.rows.length > 0) {
          setSelectedModel(json.data.rows[0].uid);
        }
      }
    });
  }, []);

  useEffect(() => {
    scrollToBottom();
  }, [messages, currentTypingMessage, scrollToBottom]);

  useEffect(() => {
    if (initMessage) {
      setMessages([]);
      setInputValue(initMessage);
    }
  }, [initMessage]);

  const streamTypeWriter = useCallback(
    (
      onToken?: (content: string) => void,
      onComplete?: (finalContent: string) => void
    ): StreamTypeController => {
      setIsTyping(true);
      setCurrentTypingMessage("");

      return {
        addToken: (token: string) => {
          setCurrentTypingMessage((prev) => {
            const newContent = prev + token;
            onToken && onToken(newContent);
            return newContent;
          });
        },
        complete: () => {
          setIsTyping(false);
          setCurrentTypingMessage((prev) => {
            const finalContent = prev;
            setCurrentTypingMessage("");
            onComplete && onComplete(finalContent);
            return "";
          });
        },
      };
    },
    []
  );

  const callOpenAI = useCallback(
    async (
      messages: OpenAIMessage[],
      isRegenerate: boolean = false,
      messageIndex?: number
    ): Promise<{ success: boolean; content?: string; error?: string }> => {
      setIsLoading(false);
      if (typeof process.env.REACT_APP_OPENAI_PROXY === "undefined") {
        console.error("no REACT_APP_OPENAI_PROXY");
        return { success: false, error: "API配置错误" };
      }
      try {
        const payload = {
          model: models?.find((value) => value.uid === selectedModel)?.model,
          messages: messages,
          stream: true,
          temperature: 0.7,
          max_tokens: 2000,
        };
        const url = process.env.REACT_APP_OPENAI_PROXY;
        console.info("api request", url, payload);
        const response = await fetch(url, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer AIzaSyCzr8KqEdaQ3cRCxsFwSHh8c7kF3RZTZWw`,
          },
          body: JSON.stringify({
            model_id: selectedModel,
            payload: payload,
          }),
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const reader = response.body?.getReader();
        if (!reader) {
          throw new Error("无法获取响应流");
        }

        const decoder = new TextDecoder();
        let buffer = "";

        const typeController = streamTypeWriter(
          (content: string) => {},
          (finalContent: string) => {
            if (isRegenerate && messageIndex !== undefined) {
              setMessages((prev) => {
                const newMessages = [...prev];
                const targetMessage = newMessages[messageIndex];
                if (targetMessage) {
                  if (!targetMessage.versions) {
                    targetMessage.versions = [
                      {
                        content: targetMessage.content,
                        model: targetMessage.model || "",
                      },
                    ];
                    targetMessage.currentVersionIndex = 0;
                  }
                  targetMessage.versions.push({
                    content: finalContent,
                    model: selectedModel,
                  });
                  targetMessage.currentVersionIndex =
                    targetMessage.versions.length - 1;
                  targetMessage.content = finalContent;
                  targetMessage.model = selectedModel;
                }
                setRefreshingMessageId(null);
                return newMessages;
              });
            } else {
              const aiMessage: Message = {
                id: Date.now(),
                type: "ai",
                content: finalContent,
                timestamp: new Date().toLocaleTimeString(),
                model: selectedModel,
                versions: [{ content: finalContent, model: selectedModel }],
                currentVersionIndex: 0,
              };
              setMessages((prev) => [...prev, aiMessage]);
              setRefreshingMessageId(null);
            }
          }
        );

        try {
          while (true) {
            const { done, value } = await reader.read();

            if (done) {
              typeController.complete();
              return { success: true, content: currentTypingMessage };
            }

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split("\n");
            buffer = lines.pop() || "";

            for (const line of lines) {
              if (line.trim() === "") continue;
              if (line.startsWith("data: ")) {
                const data = line.slice(6);

                if (data === "[DONE]") {
                  typeController.complete();
                  return { success: true, content: currentTypingMessage };
                }

                try {
                  const parsed: OpenAIStreamResponse = JSON.parse(data);
                  const delta = parsed.choices?.[0]?.delta;

                  if (delta?.content) {
                    typeController.addToken(delta.content);
                  }
                } catch (e) {
                  console.warn("解析SSE数据失败:", e);
                }
              }
            }
          }
        } catch (error) {
          console.error("读取流数据失败:", error);
          typeController.complete();
          return { success: false, error: "读取响应流失败" };
        }
      } catch (error) {
        console.error("API调用失败:", error);
        return { success: false, error: "API调用失败，请重试" };
      }
    },
    [models, selectedModel, streamTypeWriter, currentTypingMessage]
  );

  const sendMessage = useCallback(
    async (messageText: string = inputValue): Promise<void> => {
      if (!messageText.trim()) return;

      const userMessage: Message = {
        id: Date.now(),
        type: "user",
        content: messageText,
        timestamp: new Date().toLocaleTimeString(),
        versions: [{ content: messageText, model: "" }],
        currentVersionIndex: 0,
      };

      setMessages((prev) => [...prev, userMessage]);
      setInputValue("");
      setIsLoading(true);
      onChat && onChat();

      // Scroll to the new user message
      scrollToBottom();

      try {
        const conversationHistory: OpenAIMessage[] = [
          { role: "system", content: systemPrompt },
          ...messages.map((msg) => {
            const data: OpenAIMessage = {
              role: msg.type === "user" ? "user" : "assistant",
              content: msg.content,
            };
            return data;
          }),
          { role: "user", content: messageText },
        ];

        const result = await callOpenAI(conversationHistory);
        if (!result.success) {
          setMessages((prev) => [
            ...prev,
            {
              id: Date.now(),
              type: "error",
              content: result.error || "请求失败，请重试",
              timestamp: new Date().toLocaleTimeString(),
            },
          ]);
          setIsLoading(false);
        }
      } catch (error) {
        console.error("发送消息失败:", error);
        setMessages((prev) => [
          ...prev,
          {
            id: Date.now(),
            type: "error",
            content: "请求失败，请重试",
            timestamp: new Date().toLocaleTimeString(),
          },
        ]);
        setIsLoading(false);
      }
    },
    [inputValue, messages, systemPrompt, callOpenAI, scrollToBottom]
  );

  const copyMessage = useCallback(async (content: string): Promise<void> => {
    try {
      await navigator.clipboard.writeText(content);
      message.success("已复制到剪贴板");
    } catch (error) {
      console.error("复制失败:", error);
      message.error("复制失败");
    }
  }, []);

  const refreshAIResponse = useCallback(
    async (messageIndex: number): Promise<void> => {
      const userMessage = messages[messageIndex - 1];
      if (userMessage && userMessage.type === "user") {
        setRefreshingMessageId(messages[messageIndex].id);
        const conversationHistory: OpenAIMessage[] = [
          { role: "system", content: systemPrompt },
          ...messages.slice(0, messageIndex - 1).map((msg) => {
            const data: OpenAIMessage = {
              role: msg.type === "user" ? "user" : "assistant",
              content: msg.content,
            };
            return data;
          }),
          { role: "user", content: userMessage.content },
        ];

        try {
          const result = await callOpenAI(
            conversationHistory,
            true,
            messageIndex
          );
          if (!result.success) {
            setMessages((prev) => {
              const newMessages = [...prev];
              newMessages[messageIndex] = {
                id: Date.now(),
                type: "error",
                content: result.error || "重新生成失败，请重试",
                timestamp: new Date().toLocaleTimeString(),
              };
              setRefreshingMessageId(null);
              return newMessages;
            });
          } else {
            // Ensure the message type is set to "ai" on successful refresh
            setMessages((prev) => {
              const newMessages = [...prev];
              const targetMessage = newMessages[messageIndex];
              if (targetMessage) {
                targetMessage.type = "ai"; // Update type to "ai"
                targetMessage.content = result.content || "";
                targetMessage.model = selectedModel;
                if (!targetMessage.versions) {
                  targetMessage.versions = [
                    {
                      content: targetMessage.content,
                      model: targetMessage.model || "",
                    },
                  ];
                  targetMessage.currentVersionIndex = 0;
                }
                targetMessage.versions.push({
                  content: result.content || "",
                  model: selectedModel,
                });
                targetMessage.currentVersionIndex =
                  targetMessage.versions.length - 1;
              }
              setRefreshingMessageId(null);
              return newMessages;
            });
          }
        } catch (error) {
          console.error("刷新回答失败:", error);
          setMessages((prev) => {
            const newMessages = [...prev];
            newMessages[messageIndex] = {
              id: Date.now(),
              type: "error",
              content: "重新生成失败，请重试",
              timestamp: new Date().toLocaleTimeString(),
            };
            setRefreshingMessageId(null);
            return newMessages;
          });
        }
      }
    },
    [messages, systemPrompt, callOpenAI, selectedModel]
  );

  const switchMessageVersion = useCallback(
    (messageIndex: number, direction: "prev" | "next"): void => {
      setMessages((prev) => {
        const newMessages = [...prev];
        const message = newMessages[messageIndex];
        if (
          message &&
          message.versions &&
          message.currentVersionIndex !== undefined
        ) {
          const currentIndex = message.currentVersionIndex;
          const maxIndex = message.versions.length - 1;

          let newIndex = currentIndex;
          if (direction === "prev" && currentIndex > 0) {
            newIndex = currentIndex - 1;
          } else if (direction === "next" && currentIndex < maxIndex) {
            newIndex = currentIndex + 1;
          }

          if (newIndex !== currentIndex) {
            message.currentVersionIndex = newIndex;
            message.content = message.versions[newIndex].content;
            message.model = message.versions[newIndex].model;
          }
        }
        return newMessages;
      });
    },
    []
  );

  const startEditingMessage = useCallback(
    (messageIndex: number): void => {
      const message = messages[messageIndex];
      if (message && message.type === "user") {
        setEditingMessageId(message.id);
        setEditingContent(message.content);
      }
    },
    [messages]
  );

  const confirmEdit = useCallback((): void => {
    if (editingMessageId !== null) {
      setMessages((prev) => {
        const newMessages = [...prev];
        const messageIndex = newMessages.findIndex(
          (m) => m.id === editingMessageId
        );
        if (messageIndex !== -1) {
          const message = newMessages[messageIndex];
          if (!message.versions) {
            message.versions = [{ content: message.content, model: "" }];
            message.currentVersionIndex = 0;
          }
          message.versions.push({ content: editingContent, model: "" });
          message.currentVersionIndex = message.versions.length - 1;
          message.content = editingContent;
        }
        return newMessages;
      });
      setEditingMessageId(null);
      setEditingContent("");
    }
  }, [editingMessageId, editingContent]);

  const cancelEdit = useCallback((): void => {
    setEditingMessageId(null);
    setEditingContent("");
  }, []);

  const handleKeyPress = useCallback(
    (e: React.KeyboardEvent<HTMLTextAreaElement>): void => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    },
    [sendMessage]
  );

  const handleEditKeyPress = useCallback(
    (e: React.KeyboardEvent<HTMLTextAreaElement>): void => {
      if (e.key === "Enter" && e.ctrlKey) {
        e.preventDefault();
        confirmEdit();
      } else if (e.key === "Escape") {
        cancelEdit();
      }
    },
    [confirmEdit, cancelEdit]
  );

  const modelMenu: MenuProps = {
    selectedKeys: [selectedModel],
    onClick: ({ key }) => setSelectedModel(key),
    items: models?.map((model) => ({
      key: model.uid,
      label: model.name,
    })),
  };

  const refreshMenu = useCallback(
    (messageIndex: number): MenuProps => ({
      onClick: ({ key }) => {
        if (key === "refresh") {
          refreshAIResponse(messageIndex);
        }
      },
      items: [
        {
          key: "refresh",
          label: "重新生成",
        },
        {
          type: "divider",
        },
        {
          key: "model-submenu",
          label: "选择模型重新生成",
          children: models?.map((model) => ({
            key: model.uid,
            label: model.name,
            onClick: () => {
              setSelectedModel(model.uid);
              refreshAIResponse(messageIndex);
            },
          })),
        },
      ],
    }),
    [refreshAIResponse, models]
  );

  return (
    <div
      style={{
        display: "flex",
        flexDirection: "column",
        backgroundColor: "#f5f5f5",
      }}
    >
      <div style={{ flex: 1, overflowY: "auto", padding: "16px" }}>
        <Space direction="vertical" size="middle" style={{ width: "100%" }}>
          {messages.map(
            (msg, index) =>
              msg.id !== refreshingMessageId && (
                <div
                  key={msg.id}
                  style={{
                    display: "flex",
                    justifyContent:
                      msg.type === "user"
                        ? "flex-end"
                        : msg.type === "error"
                        ? "center"
                        : "flex-start",
                  }}
                >
                  <div
                    style={{
                      maxWidth: msg.type === "error" ? "100%" : "70%",
                      backgroundColor:
                        msg.type === "user"
                          ? "#1890ff"
                          : msg.type === "error"
                          ? "#fff1f0"
                          : "#ffffff",
                      color:
                        msg.type === "user"
                          ? "white"
                          : msg.type === "error"
                          ? "#ff4d4f"
                          : "black",
                      borderRadius: "8px",
                      padding: "16px",
                      position: "relative",
                      border:
                        msg.type === "ai"
                          ? "1px solid #d9d9d9"
                          : msg.type === "error"
                          ? "1px solid #ff4d4f"
                          : "none",
                      boxShadow:
                        msg.type === "ai" || msg.type === "error"
                          ? "0 1px 2px rgba(0, 0, 0, 0.03)"
                          : "none",
                      textAlign: msg.type === "error" ? "center" : "left",
                    }}
                  >
                    <div style={{ display: "flex", alignItems: "flex-start" }}>
                      <Space>
                        {msg.type !== "error" && (
                          <Avatar
                            size={32}
                            icon={
                              msg.type === "user" ? (
                                <UserOutlined />
                              ) : (
                                <RobotOutlined />
                              )
                            }
                            style={{
                              backgroundColor:
                                msg.type === "user"
                                  ? "rgb(0 132 253 / 50%)"
                                  : "#595959",
                            }}
                          />
                        )}
                        <div>
                          {msg.type !== "error" && (
                            <div
                              style={{
                                fontSize: "14px",
                                fontWeight: 500,
                                marginBottom: "4px",
                              }}
                            >
                              {msg.type === "user"
                                ? "你"
                                : msg.model
                                ? models?.find((m) => m.uid === msg.model)?.name
                                : "AI助手"}
                            </div>
                          )}
                          {editingMessageId === msg.id ? (
                            <div>
                              <TextArea
                                value={editingContent}
                                onChange={(e) =>
                                  setEditingContent(e.target.value)
                                }
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
                                <Button
                                  size="small"
                                  icon={<CloseOutlined />}
                                  onClick={cancelEdit}
                                >
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
                                <Marked text={msg.content} />
                              </div>
                              <div
                                style={{
                                  fontSize: "12px",
                                  opacity: 0.6,
                                  marginTop: "8px",
                                }}
                              >
                                {msg.timestamp}
                              </div>
                            </div>
                          )}
                        </div>
                      </Space>
                    </div>
                    <Space>
                      {msg.versions && msg.versions.length > 1 && (
                        <div style={{ marginBottom: "8px" }}>
                          <Space size="small">
                            <Button
                              size="small"
                              type="text"
                              icon={<LeftOutlined />}
                              disabled={msg.currentVersionIndex === 0}
                              onClick={() =>
                                switchMessageVersion(index, "prev")
                              }
                            />
                            <Text
                              style={{
                                fontSize: "12px",
                                color:
                                  msg.type === "user"
                                    ? "rgba(255,255,255,0.7)"
                                    : "#666",
                              }}
                            >
                              {(msg.currentVersionIndex || 0) + 1}/
                              {msg.versions.length}
                            </Text>
                            <Button
                              size="small"
                              type="text"
                              icon={<RightOutlined />}
                              disabled={
                                msg.currentVersionIndex ===
                                msg.versions.length - 1
                              }
                              onClick={() =>
                                switchMessageVersion(index, "next")
                              }
                            />
                          </Space>
                        </div>
                      )}
                      {editingMessageId !== msg.id && (
                        <div>
                          <Space size="small">
                            <Tooltip title="复制">
                              <Button
                                size="small"
                                type="text"
                                icon={<CopyOutlined />}
                                onClick={() => copyMessage(msg.content)}
                              />
                            </Tooltip>
                            {msg.type === "user" ? (
                              <Tooltip title="编辑">
                                <Button
                                  size="small"
                                  type="text"
                                  icon={<EditOutlined />}
                                  onClick={() => startEditingMessage(index)}
                                />
                              </Tooltip>
                            ) : msg.type === "error" ? (
                              <Tooltip title="重试">
                                <Button
                                  size="small"
                                  type="text"
                                  icon={<ReloadOutlined />}
                                  onClick={() => refreshAIResponse(index)}
                                />
                              </Tooltip>
                            ) : (
                              <Dropdown
                                menu={refreshMenu(index)}
                                trigger={["hover"]}
                              >
                                <Button
                                  size="small"
                                  type="text"
                                  icon={<ReloadOutlined />}
                                />
                              </Dropdown>
                            )}
                          </Space>
                        </div>
                      )}
                    </Space>
                  </div>
                </div>
              )
          )}

          {isTyping && (
            <div style={{ display: "flex", justifyContent: "flex-start" }}>
              <div
                style={{
                  maxWidth: "70%",
                  backgroundColor: "#ffffff",
                  border: "1px solid #d9d9d9",
                  borderRadius: "8px",
                  padding: "16px",
                  boxShadow: "0 1px 2px rgba(0, 0, 0, 0.03)",
                }}
              >
                <div style={{ display: "flex", alignItems: "flex-start" }}>
                  <Space>
                    <Avatar
                      size={32}
                      icon={<RobotOutlined />}
                      style={{ backgroundColor: "#595959" }}
                    />
                    <div>
                      <div
                        style={{
                          fontSize: "14px",
                          fontWeight: 500,
                          marginBottom: "4px",
                        }}
                      >
                        {models?.find((m) => m.uid === selectedModel)?.name ||
                          "AI助手"}
                      </div>
                      <Marked text={currentTypingMessage} />
                    </div>
                  </Space>
                </div>
              </div>
            </div>
          )}

          {isLoading && !isTyping && (
            <div style={{ display: "flex", justifyContent: "flex-start" }}>
              <div
                style={{
                  maxWidth: "70%",
                  backgroundColor: "#ffffff",
                  border: "1px solid #d9d9d9",
                  borderRadius: "8px",
                  padding: "16px",
                  boxShadow: "0 1px 2px rgba(0, 0, 0, 0.03)",
                }}
              >
                <div style={{ display: "flex", alignItems: "center" }}>
                  <Space>
                    <Avatar
                      size={32}
                      icon={<RobotOutlined />}
                      style={{ backgroundColor: "#595959" }}
                    />
                    <Spin size="small" />
                    <span style={{ fontSize: "14px", color: "#666" }}>
                      正在思考...
                    </span>
                  </Space>
                </div>
              </div>
            </div>
          )}
        </Space>
        <div ref={messagesEndRef} />
      </div>

      <Affix offsetBottom={10}>
        <Card style={{ borderRadius: "10px", borderColor: "#d9d9d9" }}>
          <div style={{ maxWidth: "1200px", margin: "0 auto" }}>
            <div style={{ display: "flex", marginBottom: "8px" }}>
              <TextArea
                value={inputValue}
                onChange={(e) => setInputValue(e.target.value)}
                onKeyPress={handleKeyPress}
                placeholder="提出你的问题，如：总结下面的内容..."
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
                  <Button
                    size="small"
                    type="text"
                    icon={<PaperClipOutlined />}
                  />
                </Tooltip>
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
                  onClick={() => sendMessage()}
                  disabled={!inputValue.trim() || isLoading}
                />
              </Space>
            </div>
          </div>
        </Card>
      </Affix>
    </div>
  );
};

export default AIChatComponent;
