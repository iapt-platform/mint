import React, { useState, useRef, useEffect, useCallback } from "react";
import {
  Input,
  Button,
  Dropdown,
  Tooltip,
  Space,
  type MenuProps,
  Card,
  Affix,
} from "antd";
import {
  SendOutlined,
  DownOutlined,
  PaperClipOutlined,
} from "@ant-design/icons";
import type { IAiModel, IAiModelListResponse } from "../../api/ai"; // eslint-disable-line
import { get } from "../../request";
import MsgUser from "./MsgUser";
import MsgAssistant from "./MsgAssistant";
import MsgTyping from "./MsgTyping";
import MsgLoading from "./MsgLoading";
import MsgSystem from "./MsgSystem";
import MsgError from "./MsgError";
import PromptButtonGroup from "./PromptButtonGroup";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import type { IFtsResponse } from "../fts/FullTextSearchResult";
import { siteInfo } from "../../reducers/layout";

const { TextArea } = Input;

type AIRole = "system" | "user" | "assistant" | "function";
// 类型定义
export interface MessageVersion {
  id: number;
  content: string;
  model: string;
  role: AIRole;
  timestamp: string;
}

export interface Message {
  id: number;
  type: "user" | "ai" | "error";
  versions: MessageVersion[];
}

interface OpenAIMessage {
  role: AIRole;
  content: string;
  name?: string;
}

interface StreamTypeController {
  addToken: (token: string) => void;
  complete: () => void;
}

interface OpenAIStreamResponse {
  // eslint-disable-line
  choices?: Array<{
    delta?: {
      content?: string;
    };
  }>;
}

const endOfMsg = (msg: Message) => {
  return msg.versions[msg.versions.length - 1];
};

interface IWidget {
  initMessage?: string;
  systemPrompt?: string;
  onChat?: () => void;
}

const AIChatComponent = ({
  initMessage,
  systemPrompt = `你是一个巴利语专家和佛教术语解释助手。当用户询问佛教术语时，你可以调用 searchTerm 函数来查询详细信息。

  使用方法：
  - 当用户输入类似"术语：dhamma"、"查询：karma"、"什么是 buddha"等时，调用函数查询
  - 查询结果会以结构化的方式展示，包含定义、词源、分类、详细说明等信息`,
  onChat,
}: IWidget) => {
  const [messages, setMessages] = useState<Message[]>([]);
  const [inputValue, setInputValue] = useState<string>("");
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [selectedModel, setSelectedModel] = useState<string>("");
  const [fetchModel, setFetchModel] = useState<string>("");
  const [refreshingMessageId, setRefreshingMessageId] = useState<number | null>(
    null
  );

  const messagesEndRef = useRef<HTMLDivElement>(null);
  const [isTyping, setIsTyping] = useState<boolean>(false);
  const [currentTypingMessage, setCurrentTypingMessage] = useState<string>("");
  const [models, setModels] = useState<IAiModel[]>();

  const [error, setError] = useState<string>();

  const user = useAppSelector(currentUser);
  const site = useAppSelector(siteInfo);

  const scrollToBottom = useCallback(() => {
    messagesEndRef.current?.scrollIntoView({
      behavior: "smooth",
      block: "center",
    });
  }, []);

  useEffect(() => {
    setModels(site?.settings?.models?.chat ?? []);
    if (
      site?.settings?.models?.chat &&
      site?.settings?.models?.chat.length > 0
    ) {
      setSelectedModel(site?.settings?.models?.chat[0].uid);
    }
  }, [site?.settings?.models?.chat]);

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

  const searchTerm = async (term: string) => {
    // 示例：请求你后端的百科 API
    const url = `/v2/search-pali-wbw?view=pali&key=${term}&limit=20&offset=0`;
    console.info("search api request", url);
    const res = await get<IFtsResponse>(url);
    if (res.ok) {
      console.info("search 搜索结果", res.data.rows);
      return res.data.rows;
    }
    return { error: "没有找到相关术语" };
  };

  const callOpenAI = useCallback(
    async (
      messages: OpenAIMessage[],
      modelId: string,
      isRegenerate: boolean = false,
      messageIndex?: number,
      _depth: number = 0
    ): Promise<{ success: boolean; content?: string; error?: string }> => {
      setError(undefined);
      if (typeof import.meta.env.VITE_REACT_APP_OPENAI_PROXY === "undefined") {
        console.error("no REACT_APP_OPENAI_PROXY");
        return { success: false, error: "API配置错误" };
      }

      const functions = [
        {
          name: "searchTerm",
          description: "查询佛教术语，返回百科词条",
          parameters: {
            type: "object",
            properties: {
              term: {
                type: "string",
                description: "要查询的巴利语或佛学术语",
              },
            },
            required: ["term"],
          },
        },
      ];
      try {
        setFetchModel(modelId);
        const payload: any = {
          model: models?.find((value) => value.uid === modelId)?.model,
          messages,
          stream: true,
          temperature: 0.5,
          max_tokens: 3000,
          functions,
          function_call: "auto", // 让模型决定是否调用函数
        };
        const url = import.meta.env.VITE_REACT_APP_OPENAI_PROXY;
        const data = {
          model_id: modelId,
          payload,
        };
        console.info("api request", url, data);
        setIsLoading(true);

        const response = await fetch(url, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${import.meta.env.VITE_REACT_APP_OPENAI_KEY}`,
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

        // 🔑 新增 function_call 拼接缓冲
        let functionCallName: string | null = null;
        let functionCallArgsBuffer = "";
        let functionCallInProgress = false;

        const typeController = streamTypeWriter(
          (_content: string) => {},
          (finalContent: string) => {
            console.log("newData in callOpenAI", finalContent);
            const newData: MessageVersion = {
              id: Date.now(),
              content: finalContent,
              model: modelId,
              role: "assistant",
              timestamp: new Date().toLocaleTimeString(),
            };
            if (isRegenerate && messageIndex !== undefined) {
              setMessages((prev) => {
                const newMessages = [...prev];
                const targetMessage = newMessages[messageIndex];
                if (targetMessage) {
                  if (!targetMessage.versions) {
                    targetMessage.versions = [];
                  }
                  targetMessage.versions.push(newData);
                }
                setRefreshingMessageId(null);
                return newMessages;
              });
            } else {
              const aiMessage: Message = {
                id: Date.now(),
                type: "ai",
                versions: [newData],
              };
              setMessages((prev) => [...prev, aiMessage]);
              setRefreshingMessageId(null);
            }
          }
        );

        // ✅ 安全 parse
        const safeParseArgs = (s: string) => {
          try {
            return JSON.parse(s);
          } catch {
            return {};
          }
        };

        // 🔑 处理 function_call 完成时执行函数 + 再次请求
        const handleFunctionCallAndReask = async () => {
          const argsObj = safeParseArgs(functionCallArgsBuffer);
          console.log("完整 arguments:", functionCallArgsBuffer, argsObj);

          if (functionCallName === "searchTerm") {
            const result = await searchTerm(
              argsObj.term || argsObj.query || ""
            );
            const followUp: OpenAIMessage[] = [
              ...messages,
              {
                role: "function",
                name: "searchTerm",
                content: JSON.stringify(result),
              },
            ];
            console.log("search 再次请求", followUp);
            return await callOpenAI(
              followUp,
              modelId,
              isRegenerate,
              messageIndex,
              _depth + 1
            );
          }
          return { success: false, error: "未知函数: " + functionCallName };
        };

        try {
          while (true) {
            const { done, value } = await reader.read();
            if (done) {
              if (functionCallInProgress && functionCallName) {
                const res = await handleFunctionCallAndReask();
                setIsLoading(false);
                return res;
              }
              typeController.complete();
              setIsLoading(false);
              return { success: true, content: "" };
            }

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split("\n");
            buffer = lines.pop() || "";

            for (const line of lines) {
              if (!line.trim() || !line.startsWith("data: ")) continue;
              const data = line.slice(6);
              if (data === "[DONE]") {
                if (functionCallInProgress && functionCallName) {
                  const res = await handleFunctionCallAndReask();
                  setIsLoading(false);
                  return res;
                }
                typeController.complete();
                setIsLoading(false);
                return { success: true, content: "" };
              }

              let parsed: any = null;
              try {
                parsed = JSON.parse(data);
              } catch {
                continue;
              }

              const delta = parsed.choices?.[0]?.delta;
              const finish_reason = parsed.choices?.[0]?.finish_reason;

              // 🔑 拼接 function_call
              if (delta?.function_call) {
                if (delta.function_call.name) {
                  functionCallName = delta.function_call.name;
                }
                if (typeof delta.function_call.arguments === "string") {
                  functionCallInProgress = true;
                  functionCallArgsBuffer += delta.function_call.arguments;
                }
              }

              // 正常文本输出
              if (delta?.content && !functionCallInProgress) {
                typeController.addToken(delta.content);
              }

              // function_call 完成
              if (finish_reason === "function_call") {
                const res = await handleFunctionCallAndReask();
                setIsLoading(false);
                return res;
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
    [models, streamTypeWriter, currentTypingMessage]
  );

  const sendMessage = useCallback(
    async (messageText: string = inputValue): Promise<void> => {
      if (!messageText.trim()) return;

      const newData: MessageVersion = {
        id: Date.now(),
        content: messageText,
        model: "",
        role: "user",
        timestamp: new Date().toLocaleTimeString(),
      };
      const userMessage: Message = {
        id: Date.now(),
        type: "user",
        versions: [newData],
      };

      setMessages((prev) => [...prev, userMessage]);
      setInputValue("");
      setIsLoading(true);

      // Scroll to the new user message
      scrollToBottom();

      try {
        const conversationHistory: OpenAIMessage[] = [
          { role: "system", content: systemPrompt },
          ...messages.map((msg) => {
            const data: OpenAIMessage = {
              role: msg.type === "user" ? "user" : "assistant",
              content: msg.versions[msg.versions.length - 1].content,
            };
            return data;
          }),
          { role: "user", content: messageText },
        ];

        const result = await callOpenAI(conversationHistory, selectedModel);
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
    [
      inputValue,
      scrollToBottom,
      systemPrompt,
      messages,
      callOpenAI,
      selectedModel,
    ]
  );

  const refreshAIResponse = useCallback(
    async (messageIndex: number, modelId: string): Promise<void> => {
      console.debug("refresh", messageIndex);
      const userMessage = messages[messageIndex - 1];
      if (userMessage && userMessage.type === "user") {
        setRefreshingMessageId(messages[messageIndex].id);
        const conversationHistory: OpenAIMessage[] = [
          { role: "system", content: systemPrompt },
          ...messages.slice(0, messageIndex - 1).map((msg) => {
            const data: OpenAIMessage = {
              role: msg.type === "user" ? "user" : "assistant",
              content: endOfMsg(msg).content,
            };
            return data;
          }),
          { role: "user", content: endOfMsg(userMessage).content },
        ];

        try {
          const result = await callOpenAI(
            conversationHistory,
            modelId,
            true,
            messageIndex
          );
          setIsLoading(false);
          if (!result.success) {
            setError("重新生成失败，请重试");
            setRefreshingMessageId(null);
          } else {
            /*
            console.log("newData refreshAIResponse", result);
            setMessages((prev) => {
              const newMessages = [...prev];
              const targetMessage = newMessages[messageIndex];
              if (targetMessage) {
                const newData: MessageVersion = {
                  id: Date.now(),
                  content: result.content || "",
                  model: modelId,
                  role: "assistant",
                  timestamp: new Date().toLocaleTimeString(),
                };
                targetMessage.type = "ai"; // Update type to "ai"
                if (!targetMessage.versions) {
                  targetMessage.versions = [];
                }
                targetMessage.versions.push(newData);
              }
              setRefreshingMessageId(null);
              return newMessages;
            });
            */
          }
        } catch (error) {
          console.error("刷新回答失败:", error);
          setIsLoading(false);
          setError("请求失败，请重试");
          setRefreshingMessageId(null);
        }
      }
    },
    [messages, systemPrompt, callOpenAI]
  );

  const confirmEdit = useCallback((id: number, text: string): void => {
    setMessages((prev) => {
      const newMessages = [...prev];
      const messageIndex = newMessages.findIndex((m) => m.id === id);
      if (messageIndex !== -1) {
        const message = newMessages[messageIndex];
        if (!message.versions) {
          message.versions = [];
        }
        const newData: MessageVersion = {
          id: Date.now(),
          content: text,
          model: "",
          role: "user",
          timestamp: new Date().toLocaleTimeString(),
        };
        message.versions.push(newData);
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
    onClick: ({ key }) => {
      console.log("setSelectedModel", key);
      setSelectedModel(key);
    },
    items: models?.map((model) => ({
      key: model.uid,
      label: model.name,
    })),
  };

  return user ? (
    <div
      style={{
        display: "flex",
        flexDirection: "column",
        width: "100%",
      }}
    >
      <div style={{ flex: 1, overflowY: "auto", padding: "16px" }}>
        <Space orientation="vertical" size="middle" style={{ width: "100%" }}>
          <MsgSystem value={systemPrompt} />
          {messages.map((msg, index) => {
            if (msg.id === refreshingMessageId) {
              return <></>;
            } else {
              if (msg.type === "user") {
                return (
                  <MsgUser
                    key={index}
                    msg={msg}
                    onChange={(value: string) => confirmEdit(index, value)}
                  />
                );
              } else if (msg.type === "ai") {
                return (
                  <MsgAssistant
                    key={index}
                    msg={msg}
                    models={models}
                    onRefresh={(modelId: string) => {
                      refreshAIResponse(index, modelId);
                    }}
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
              onRefresh={() =>
                refreshAIResponse(messages.length - 1, fetchModel)
              }
            />
          ) : (
            <></>
          )}
          {isTyping && (
            <MsgTyping
              text={currentTypingMessage}
              model={models?.find((m) => m.uid === fetchModel)}
            />
          )}

          {isLoading && !isTyping && (
            <MsgLoading model={models?.find((m) => m.uid === fetchModel)} />
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
                  onClick={() => {
                    sendMessage();
                    onChat && onChat();
                  }}
                  disabled={!inputValue.trim() || isLoading}
                />
              </Space>
            </div>
          </div>
        </Card>
      </Affix>
    </div>
  ) : (
    <></>
  );
};

export default AIChatComponent;
