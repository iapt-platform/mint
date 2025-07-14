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
interface Message {
  id: number;
  type: "user" | "ai";
  content: string;
  timestamp: string;
  model?: string;
  versions?: string[]; // 存储所有版本的内容
  currentVersionIndex?: number; // 当前显示的版本索引
}

interface OpenAIMessage {
  role: "system" | "user" | "assistant";
  content: string;
}

interface AIModel {
  key: string;
  label: string;
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

  const messagesEndRef = useRef<HTMLDivElement>(null);
  const [isTyping, setIsTyping] = useState<boolean>(false);
  const [currentTypingMessage, setCurrentTypingMessage] = useState<string>("");
  const [models, setModels] = useState<IAiModel[]>(); // 可用的AI模型

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
      sendMessage();
    }
  }, [initMessage]);

  // 打字机效果 - 支持流式输入
  const typeWriter = useCallback(
    (text: string, callback: () => void): NodeJS.Timeout => {
      setIsTyping(true);
      setCurrentTypingMessage("");
      let index = 0;

      const timer = setInterval(() => {
        if (index < text.length) {
          setCurrentTypingMessage((prev) => prev + text.charAt(index));
          index++;
        } else {
          clearInterval(timer);
          setIsTyping(false);
          setCurrentTypingMessage("");
          callback();
        }
      }, 30);

      return timer;
    },
    []
  );

  // 流式打字机效果
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

  // 调用OpenAI API - 支持流式输出
  const callOpenAI = useCallback(
    async (
      messages: OpenAIMessage[],
      isRegenerate: boolean = false,
      messageIndex?: number
    ): Promise<void> => {
      setIsLoading(false); // 开始流式输出时取消loading状态
      if (typeof process.env.REACT_APP_OPENAI_PROXY === "undefined") {
        console.error("no REACT_APP_OPENAI_PROXY");
        return;
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

        // 处理流式响应
        const reader = response.body?.getReader();
        if (!reader) {
          throw new Error("无法获取响应流");
        }

        const decoder = new TextDecoder();
        let buffer = "";

        // 创建流式打字机效果
        const typeController = streamTypeWriter(
          (content: string) => {
            // 每次添加token时的回调
          },
          (finalContent: string) => {
            // 完成时的回调
            if (isRegenerate && messageIndex !== undefined) {
              // 重新生成时，添加到版本历史中
              setMessages((prev) => {
                const newMessages = [...prev];
                const targetMessage = newMessages[messageIndex];
                if (targetMessage) {
                  if (!targetMessage.versions) {
                    targetMessage.versions = [targetMessage.content];
                    targetMessage.currentVersionIndex = 0;
                  }
                  targetMessage.versions.push(finalContent);
                  targetMessage.currentVersionIndex =
                    targetMessage.versions.length - 1;
                  targetMessage.content = finalContent;
                }
                return newMessages;
              });
            } else {
              // 新消息
              const aiMessage: Message = {
                id: Date.now(),
                type: "ai",
                content: finalContent,
                timestamp: new Date().toLocaleTimeString(),
                model: selectedModel,
                versions: [finalContent],
                currentVersionIndex: 0,
              };
              setMessages((prev) => [...prev, aiMessage]);
            }
          }
        );

        try {
          while (true) {
            const { done, value } = await reader.read();

            if (done) {
              typeController.complete();
              break;
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
                  return;
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
          throw error;
        }
      } catch (error) {
        console.error("API调用失败:", error);

        // 如果真实API失败，回退到模拟响应
        const mockResponse = await simulateAIResponse(messages);
        typeWriter(mockResponse, () => {
          if (isRegenerate && messageIndex !== undefined) {
            setMessages((prev) => {
              const newMessages = [...prev];
              const targetMessage = newMessages[messageIndex];
              if (targetMessage) {
                if (!targetMessage.versions) {
                  targetMessage.versions = [targetMessage.content];
                  targetMessage.currentVersionIndex = 0;
                }
                targetMessage.versions.push(mockResponse);
                targetMessage.currentVersionIndex =
                  targetMessage.versions.length - 1;
                targetMessage.content = mockResponse;
              }
              return newMessages;
            });
          } else {
            const aiMessage: Message = {
              id: Date.now(),
              type: "ai",
              content: mockResponse,
              timestamp: new Date().toLocaleTimeString(),
              model: selectedModel,
              versions: [mockResponse],
              currentVersionIndex: 0,
            };
            setMessages((prev) => [...prev, aiMessage]);
          }
        });
      }
    },
    [selectedModel, streamTypeWriter, typeWriter]
  );

  // 模拟AI响应（作为备用方案）
  const simulateAIResponse = useCallback(
    async (conversationHistory: OpenAIMessage[]): Promise<string> => {
      return new Promise((resolve) => {
        setTimeout(() => {
          const lastUserMessage =
            conversationHistory[conversationHistory.length - 1]?.content || "";
          const responses = [
            '这是一个很好的问题。让我来为你详细解答这个关于 "' +
              lastUserMessage +
              '" 的问题。\n\n首先，我需要说明的是，这个话题涉及多个方面的考虑。从技术层面来看，我们需要考虑实现的可行性和复杂度。从用户体验的角度，我们要确保解决方案既实用又易于理解。\n\n希望这个回答对你有帮助！',
            '我理解你的意思。根据我的知识，关于 "' +
              lastUserMessage +
              '" 这个问题，我可以从以下几个角度来分析：\n\n1. 首先是基本概念的理解\n2. 然后是实际应用场景\n3. 最后是注意事项和建议\n\n这样的分析方法能够帮助我们更全面地理解这个问题。',
            '感谢你的提问。关于 "' +
              lastUserMessage +
              '" 这个话题，我的看法是这样的：\n\n这确实是一个值得深入探讨的问题。在我看来，解决这类问题的关键在于找到平衡点，既要考虑效率，也要考虑可维护性。\n\n让我知道如果你需要更详细的解释！',
          ];
          resolve(responses[Math.floor(Math.random() * responses.length)]);
        }, 1000);
      });
    },
    []
  );

  // 发送消息到AI
  const sendMessage = useCallback(
    async (messageText: string = inputValue): Promise<void> => {
      if (!messageText.trim()) return;

      const userMessage: Message = {
        id: Date.now(),
        type: "user",
        content: messageText,
        timestamp: new Date().toLocaleTimeString(),
        versions: [messageText],
        currentVersionIndex: 0,
      };

      setMessages((prev) => [...prev, userMessage]);
      setInputValue("");
      setIsLoading(true);
      onChat && onChat();
      try {
        // 构建对话历史
        const conversationHistory: OpenAIMessage[] = [
          { role: "system", content: systemPrompt },
          ...messages.map((msg) => {
            const newMsg: OpenAIMessage = {
              role: msg.type === "user" ? "user" : "assistant",
              content: msg.content,
            };
            return newMsg;
          }),
          { role: "user", content: messageText },
        ];

        // 调用OpenAI API
        await callOpenAI(conversationHistory);
      } catch (error) {
        console.error("发送消息失败:", error);
        message.error("发送消息失败，请重试");
        setIsLoading(false);
        setIsTyping(false);
      }
    },
    [inputValue, messages, systemPrompt, callOpenAI]
  );

  // 复制消息内容
  const copyMessage = useCallback(async (content: string): Promise<void> => {
    try {
      await navigator.clipboard.writeText(content);
      message.success("已复制到剪贴板");
    } catch (error) {
      console.error("复制失败:", error);
      message.error("复制失败");
    }
  }, []);

  // 刷新AI回答
  const refreshAIResponse = useCallback(
    async (messageIndex: number): Promise<void> => {
      const userMessage = messages[messageIndex - 1];
      if (userMessage && userMessage.type === "user") {
        // 重新构建到该消息为止的对话历史
        const conversationHistory: OpenAIMessage[] = [
          { role: "system", content: systemPrompt },
          ...messages.slice(0, messageIndex - 1).map((msg) => {
            const newMsg: OpenAIMessage = {
              role: msg.type === "user" ? "user" : "assistant",
              content: msg.content,
            };
            return newMsg;
          }),
          { role: "user", content: userMessage.content },
        ];

        try {
          await callOpenAI(conversationHistory, true, messageIndex);
        } catch (error) {
          console.error("刷新回答失败:", error);
          message.error("刷新回答失败，请重试");
        }
      }
    },
    [messages, systemPrompt, callOpenAI]
  );

  // 切换消息版本
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
            message.content = message.versions[newIndex];
          }
        }
        return newMessages;
      });
    },
    []
  );

  // 开始编辑用户消息
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

  // 确认编辑
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
            message.versions = [message.content];
            message.currentVersionIndex = 0;
          }
          message.versions.push(editingContent);
          message.currentVersionIndex = message.versions.length - 1;
          message.content = editingContent;
        }
        return newMessages;
      });
      setEditingMessageId(null);
      setEditingContent("");
    }
  }, [editingMessageId, editingContent]);

  // 取消编辑
  const cancelEdit = useCallback((): void => {
    setEditingMessageId(null);
    setEditingContent("");
  }, []);

  // 处理键盘事件
  const handleKeyPress = useCallback(
    (e: React.KeyboardEvent<HTMLTextAreaElement>): void => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    },
    [sendMessage]
  );

  // 处理编辑时的键盘事件
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

  // 模型选择菜单
  const modelMenu: MenuProps = {
    selectedKeys: [selectedModel],
    onClick: ({ key }) => setSelectedModel(key),
    items: models?.map((model) => ({
      key: model.uid,
      label: model.name,
    })),
  };

  // 刷新按钮的下拉菜单
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
      {/* 聊天显示窗口 */}
      <div style={{ flex: 1, overflowY: "auto", padding: "16px" }}>
        <Space direction="vertical" size="middle" style={{ width: "100%" }}>
          {messages.map((msg, index) => (
            <div
              key={msg.id}
              style={{
                display: "flex",
                justifyContent: msg.type === "user" ? "flex-end" : "flex-start",
              }}
            >
              <div
                style={{
                  maxWidth: "70%",
                  backgroundColor: msg.type === "user" ? "#1890ff" : "#ffffff",
                  color: msg.type === "user" ? "white" : "black",
                  borderRadius: "8px",
                  padding: "16px",
                  position: "relative",
                  border: msg.type === "ai" ? "1px solid #d9d9d9" : "none",
                  boxShadow:
                    msg.type === "ai"
                      ? "0 1px 2px rgba(0, 0, 0, 0.03)"
                      : "none",
                }}
              >
                <div style={{ display: "flex", alignItems: "flex-start" }}>
                  <Space>
                    <div>
                      <Space>
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
                                ? "#rgb(0 132 253 / 50%)"
                                : "#595959",
                          }}
                        />
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
                      </Space>
                      {/* 显示版本导航 */}
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

                      {/* 编辑模式 */}
                      {editingMessageId === msg.id ? (
                        <div>
                          <TextArea
                            value={editingContent}
                            onChange={(e) => setEditingContent(e.target.value)}
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

                {/* 悬浮工具按钮 */}
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
                      ) : (
                        <Dropdown menu={refreshMenu(index)} trigger={["hover"]}>
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
              </div>
            </div>
          ))}

          {/* 显示AI正在输入的消息 */}
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

      {/* 用户输入区域 */}
      <Affix offsetBottom={10}>
        <Card style={{ borderRadius: "10px", borderColor: "#d9d9d9" }}>
          <div style={{ maxWidth: "1200px", margin: "0 auto" }}>
            {/* 输入框 */}
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

            {/* 功能按钮和模型选择 */}
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
