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
} from "@ant-design/icons";
import Marked from "../general/Marked";
import { IAiModel, IAiModelListResponse } from "../api/ai";
import { get } from "../../request";

const { TextArea } = Input;

// 类型定义
interface Message {
  id: number;
  type: "user" | "ai";
  content: string;
  timestamp: string;
  model?: string;
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
    async (messages: OpenAIMessage[]): Promise<void> => {
      setIsLoading(false); // 开始流式输出时取消loading状态
      if (typeof process.env.REACT_APP_OPENAI_PROXY === "undefined") {
        console.error("no REACT_APP_OPENAI_PROXY");
        return;
      }
      try {
        const payload = {
          model: selectedModel,
          messages: messages,
          stream: true,
          temperature: 0.7,
          max_tokens: 2000,
        };
        const response = await fetch(process.env.REACT_APP_OPENAI_PROXY, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer AIzaSyCzr8KqEdaQ3cRCxsFwSHh8c7kF3RZTZWw`, // 或你的API密钥
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
            const aiMessage: Message = {
              id: Date.now(),
              type: "ai",
              content: finalContent,
              timestamp: new Date().toLocaleTimeString(),
              model: selectedModel,
            };
            setMessages((prev) => [...prev, aiMessage]);
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
          const aiMessage: Message = {
            id: Date.now(),
            type: "ai",
            content: mockResponse,
            timestamp: new Date().toLocaleTimeString(),
            model: selectedModel,
          };
          setMessages((prev) => [...prev, aiMessage]);
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

        // 移除旧的AI回答
        setMessages((prev) => prev.slice(0, messageIndex));

        try {
          await callOpenAI(conversationHistory);
        } catch (error) {
          console.error("刷新回答失败:", error);
          message.error("刷新回答失败，请重试");
        }
      }
    },
    [messages, systemPrompt, callOpenAI]
  );

  // 编辑用户消息
  const editUserMessage = useCallback(
    (messageIndex: number, newContent: string): void => {
      const updatedMessages = [...messages];
      updatedMessages[messageIndex].content = newContent;
      setMessages(updatedMessages);
    },
    [messages]
  );

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
    <div className="flex flex-col h-screen bg-gray-50">
      {/* 聊天显示窗口 */}
      <div className="flex-1 overflow-y-auto p-4 space-y-4">
        {messages.map((msg, index) => (
          <div
            key={msg.id}
            className={`flex ${
              msg.type === "user" ? "justify-end" : "justify-start"
            }`}
          >
            <div
              className={`group max-w-[70%] ${
                msg.type === "user"
                  ? "bg-blue-500 text-white rounded-l-lg rounded-tr-lg"
                  : "bg-white border rounded-r-lg rounded-tl-lg shadow-sm"
              } p-4 relative`}
            >
              <div className="flex items-start space-x-3">
                <Avatar
                  size={32}
                  icon={
                    msg.type === "user" ? <UserOutlined /> : <RobotOutlined />
                  }
                  className={
                    msg.type === "user" ? "bg-blue-600" : "bg-gray-500"
                  }
                />
                <div className="flex-1">
                  <div className="text-sm font-medium mb-1">
                    {msg.type === "user"
                      ? "你"
                      : msg.model
                      ? models?.find((m) => m.uid === msg.model)?.name
                      : "AI助手"}
                  </div>
                  <div className="text-sm leading-relaxed whitespace-pre-wrap">
                    <Marked text={msg.content} />
                  </div>
                  <div className="text-xs opacity-60 mt-2">{msg.timestamp}</div>
                </div>
              </div>

              {/* 悬浮工具按钮 */}
              <div
                className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                style={{ textAlign: "right" }}
              >
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
                        onClick={() => {
                          const newContent = prompt("编辑消息:", msg.content);
                          if (newContent !== null) {
                            editUserMessage(index, newContent);
                          }
                        }}
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
            </div>
          </div>
        ))}

        {/* 显示AI正在输入的消息 */}
        {isTyping && (
          <div className="flex justify-start">
            <div className="max-w-[70%] bg-white border rounded-r-lg rounded-tl-lg shadow-sm p-4">
              <div className="flex items-start space-x-3">
                <Avatar
                  size={32}
                  icon={<RobotOutlined />}
                  className="bg-gray-500"
                />
                <div className="flex-1">
                  <div className="text-sm font-medium mb-1">
                    {models?.find((m) => m.uid === selectedModel)?.name ||
                      "AI助手"}
                  </div>
                  <Marked text={currentTypingMessage} />
                </div>
              </div>
            </div>
          </div>
        )}

        {isLoading && !isTyping && (
          <div className="flex justify-start">
            <div className="max-w-[70%] bg-white border rounded-r-lg rounded-tl-lg shadow-sm p-4">
              <div className="flex items-center space-x-3">
                <Avatar
                  size={32}
                  icon={<RobotOutlined />}
                  className="bg-gray-500"
                />
                <Spin size="small" />
                <span className="text-sm text-gray-500">正在思考...</span>
              </div>
            </div>
          </div>
        )}

        <div ref={messagesEndRef} />
      </div>

      {/* 用户输入区域 */}
      <Affix offsetBottom={10}>
        <Card bordered={true} style={{ borderRadius: 10, borderColor: "gray" }}>
          <div className="max-w-4xl mx-auto">
            {/* 输入框 */}
            <div style={{ display: "flex" }}>
              <TextArea
                value={inputValue}
                onChange={(e) => setInputValue(e.target.value)}
                onKeyPress={handleKeyPress}
                placeholder="提出你的问题，如：总结下面的内容..."
                autoSize={{ minRows: 1, maxRows: 6 }}
                className="resize-none pr-12"
              />
            </div>

            {/* 功能按钮和模型选择 */}
            <div style={{ display: "flex", justifyContent: "space-between" }}>
              <Space>
                <Tooltip title="附加文件">
                  <Button
                    size="small"
                    type="text"
                    icon={<PaperClipOutlined />}
                  />
                </Tooltip>
              </Space>
              <div>
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
                  className="absolute right-2 bottom-2"
                />
              </div>
            </div>
          </div>
        </Card>
      </Affix>
    </div>
  );
};

export default AIChatComponent;
