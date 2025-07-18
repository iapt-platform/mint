import React, { useState, useRef, useEffect, useCallback } from "react";
import {
  Input,
  Button,
  Dropdown,
  Tooltip,
  Space,
  MenuProps,
  Card,
  Affix,
} from "antd";
import {
  SendOutlined,
  DownOutlined,
  PaperClipOutlined,
} from "@ant-design/icons";
import { IAiModel, IAiModelListResponse } from "../api/ai";
import { get } from "../../request";
import MsgUser from "./MsgUser";
import MsgAssistant from "./MsgAssistant";
import MsgTyping from "./MsgTyping";
import MsgLoading from "./MsgLoading";
import MsgSystem from "./MsgSystem";
import MsgError from "./MsgError";

const { TextArea } = Input;

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
  const [refreshingMessageId, setRefreshingMessageId] = useState<number | null>(
    null
  );

  const messagesEndRef = useRef<HTMLDivElement>(null);
  const [isTyping, setIsTyping] = useState<boolean>(false);
  const [currentTypingMessage, setCurrentTypingMessage] = useState<string>("");
  const [models, setModels] = useState<IAiModel[]>();

  const [error, setError] = useState<string>();

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
      setError(undefined);
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
        const data = {
          model_id: selectedModel,
          payload: payload,
        };
        console.info("api request", url, data);
        setIsLoading(true);
        const response = await fetch(url, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer AIzaSyCzr8KqEdaQ3cRCxsFwSHh8c7kF3RZTZWw`,
          },
          body: JSON.stringify(data),
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
        setIsLoading(false);
        if (!result.success) {
          setError("请求失败，请重试");
        }
      } catch (error) {
        console.error("发送消息失败:", error);
        setError("请求失败，请重试");
        setIsLoading(false);
      }
    },
    [inputValue, messages, systemPrompt, callOpenAI, scrollToBottom]
  );

  const refreshAIResponse = useCallback(
    async (messageIndex: number): Promise<void> => {
      console.debug("refresh", messageIndex);
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
          setIsLoading(false);
          if (!result.success) {
            setError("重新生成失败，请重试");
            setRefreshingMessageId(null);
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
          setIsLoading(false);
          setError("请求失败，请重试");
          setRefreshingMessageId(null);
        }
      }
    },
    [messages, systemPrompt, callOpenAI, selectedModel]
  );

  const confirmEdit = useCallback((id: number, text: string): void => {
    setMessages((prev) => {
      const newMessages = [...prev];
      const messageIndex = newMessages.findIndex((m) => m.id === id);
      if (messageIndex !== -1) {
        const message = newMessages[messageIndex];
        if (!message.versions) {
          message.versions = [{ content: message.content, model: "" }];
          message.currentVersionIndex = 0;
        }
        message.versions.push({ content: text, model: "" });
        message.currentVersionIndex = message.versions.length - 1;
        message.content = text;
      }
      return newMessages;
    });
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

  const modelMenu: MenuProps = {
    selectedKeys: [selectedModel],
    onClick: ({ key }) => setSelectedModel(key),
    items: models?.map((model) => ({
      key: model.uid,
      label: model.name,
    })),
  };

  return (
    <div
      style={{
        display: "flex",
        flexDirection: "column",
        width: "100%",
      }}
    >
      <div style={{ flex: 1, overflowY: "auto", padding: "16px" }}>
        <Space direction="vertical" size="middle" style={{ width: "100%" }}>
          <MsgSystem value={systemPrompt} />
          {messages.map((msg, index) => {
            if (msg.id === refreshingMessageId) {
              return <></>;
            } else {
              if (msg.type === "user") {
                return (
                  <MsgUser
                    msg={msg}
                    onChange={(value: string) => confirmEdit(index, value)}
                  />
                );
              } else if (msg.type === "ai") {
                return (
                  <MsgAssistant
                    msg={msg}
                    models={models}
                    onRefresh={() => refreshAIResponse(index)}
                  />
                );
              } else {
                return <>unknown</>;
              }
            }
          })}
          {error ? (
            <MsgError
              message={error}
              onRefresh={() => refreshAIResponse(messages.length - 1)}
            />
          ) : (
            <></>
          )}
          {isTyping && (
            <MsgTyping
              text={currentTypingMessage}
              model={models?.find((m) => m.uid === selectedModel)}
            />
          )}

          {isLoading && !isTyping && (
            <MsgLoading model={models?.find((m) => m.uid === selectedModel)} />
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
