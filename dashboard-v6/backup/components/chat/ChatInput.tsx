import React, { useState, useCallback, useEffect, useRef } from "react";
import {
  Affix,
  AutoComplete,
  Button,
  Card,
  Dropdown,
  Input,
  type MenuProps,
  Select,
  Space,
  Tooltip,
  Typography,
  Tag,
} from "antd";
import {
  SendOutlined,
  PaperClipOutlined,
  DownOutlined,
  SearchOutlined,
} from "@ant-design/icons";
import type { ChatInputProps } from "../../types/chat";
import PromptButtonGroup from "./PromptButtonGroup";
import type { IAiModel } from "../../api/ai";
import { useAppSelector } from "../../hooks";
import { siteInfo } from "../../reducers/layout";
import { backend, _get } from "../../request";
import type { SuggestionsResponse } from "../../types/search";

const { TextArea } = Input;
const { Text } = Typography;

// 定义建议项类型
interface SuggestionOption {
  value: string;
  label: React.ReactNode;
  text: string;
  source: string;
  score: number;
  resource_type?: string;
  language?: string;
  doc_id?: string;
}

// 定义搜索模式类型
type SearchMode = "auto" | "none" | "team" | "word" | "explain" | "title";

export function ChatInput({
  onSend,
  onModelChange,
  disabled,
  placeholder,
}: ChatInputProps) {
  const [inputValue, setInputValue] = useState("");
  const [selectedModel, setSelectedModel] = useState<string>("");
  const [models, setModels] = useState<IAiModel[]>();
  const [searchMode, setSearchMode] = useState<SearchMode>("auto");
  const [suggestions, setSuggestions] = useState<SuggestionOption[]>([]);
  const [loading, setLoading] = useState(false);
  const site = useAppSelector(siteInfo);

  // 使用 ref 来防止过于频繁的请求
  const abortControllerRef = useRef<AbortController | null>(null);
  const debounceTimerRef = useRef<NodeJS.Timeout | null>(null);

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

  // 获取搜索建议
  const fetchSuggestions = useCallback(
    async (query: string) => {
      // 取消之前的请求
      if (abortControllerRef.current) {
        abortControllerRef.current.abort();
      }

      // 如果查询为空或搜索模式为 none，清空建议
      if (!query.trim() || searchMode === "none") {
        setSuggestions([]);
        return;
      }

      // 创建新的 AbortController
      abortControllerRef.current = new AbortController();

      setLoading(true);

      try {
        // 根据搜索模式确定查询字段
        let fields: string | undefined;
        switch (searchMode) {
          case "title":
            fields = "title";
            break;
          case "team":
          case "word":
          case "explain":
            fields = "title,content";
            break;
          case "auto":
          default:
            // 不指定 fields，查询所有字段
            fields = undefined;
        }

        // 构建查询参数
        const params = new URLSearchParams({
          q: query,
          limit: "10",
        });

        if (fields) {
          params.append("fields", fields);
        }

        // 发起请求
        const url = `/v3/search-suggest?${params.toString()}`;
        // 发起请求
        const response = await fetch(backend(url), {
          signal: abortControllerRef.current.signal,
        });

        if (!response.ok) {
          throw new Error("搜索建议请求失败");
        }

        const data: SuggestionsResponse = await response.json();

        if (data.success && data.data.suggestions) {
          // 转换为 AutoComplete 选项格式
          const options: SuggestionOption[] = data.data.suggestions.map(
            (item: any) => ({
              value: item.text,
              label: renderSuggestionItem(item),
              text: item.text,
              source: item.source,
              score: item.score,
              resource_type: item.resource_type,
              language: item.language,
              doc_id: item.doc_id,
            })
          );

          setSuggestions(options);
        } else {
          setSuggestions([]);
        }
      } catch (error: any) {
        // 忽略取消的请求
        if (error.name === "AbortError") {
          return;
        }
        console.error("获取搜索建议失败:", error);
        setSuggestions([]);
      } finally {
        setLoading(false);
      }
    },
    [searchMode]
  );

  // 渲染建议项
  const renderSuggestionItem = (item: any) => {
    // 来源标签颜色映射
    const sourceColors: Record<string, string> = {
      title: "blue",
      content: "green",
      page_refs: "orange",
    };

    // 语言标签
    const languageLabels: Record<string, string> = {
      pali: "巴利文",
      zh: "中文",
      en: "英文",
    };

    return (
      <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
        <span style={{ flex: 1 }}>{item.text}</span>
        <Space size={4}>
          {item.source && (
            <Tag
              color={sourceColors[item.source] || "default"}
              style={{ margin: 0, fontSize: "12px" }}
            >
              {item.source}
            </Tag>
          )}
          {item.language && (
            <Tag style={{ margin: 0, fontSize: "12px" }}>
              {languageLabels[item.language] || item.language}
            </Tag>
          )}
        </Space>
      </div>
    );
  };

  // 处理输入变化（带防抖）
  const handleInputChange = useCallback(
    (value: string) => {
      setInputValue(value);

      // 清除之前的定时器
      if (debounceTimerRef.current) {
        clearTimeout(debounceTimerRef.current);
      }

      // 如果输入为空，直接清空建议
      if (!value.trim()) {
        setSuggestions([]);
        return;
      }

      // 设置新的防抖定时器（300ms）
      debounceTimerRef.current = setTimeout(() => {
        fetchSuggestions(value);
      }, 300);
    },
    [fetchSuggestions]
  );

  // 处理选择建议项
  const handleSelect = useCallback(
    (value: string, _option: SuggestionOption) => {
      setInputValue(value);
      // 选择后清空建议列表
      setSuggestions([]);
    },
    []
  );

  const handleSend = useCallback(() => {
    if (!inputValue.trim() || disabled) return;

    onSend(inputValue.trim());
    setInputValue("");
    setSuggestions([]);
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

  const handleSearchModeChange = (value: SearchMode) => {
    setSearchMode(value);
    // 模式改变时，如果有输入内容，重新获取建议
    if (inputValue.trim() && value !== "none") {
      fetchSuggestions(inputValue);
    } else {
      setSuggestions([]);
    }
  };

  // 组件卸载时清理
  useEffect(() => {
    return () => {
      if (abortControllerRef.current) {
        abortControllerRef.current.abort();
      }
      if (debounceTimerRef.current) {
        clearTimeout(debounceTimerRef.current);
      }
    };
  }, []);

  return (
    <Affix offsetBottom={10}>
      <Card style={{ borderRadius: "10px", borderColor: "#d9d9d9" }}>
        <div style={{ maxWidth: "1200px", margin: "0 auto" }}>
          <div style={{ display: "flex", marginBottom: "8px", gap: "8px" }}>
            <Space>
              <SearchOutlined />
              <Select
                placement="topLeft"
                value={searchMode}
                style={{ width: 120 }}
                onChange={handleSearchModeChange}
                options={[
                  {
                    value: "auto",
                    label: (
                      <div>
                        <div>{"自动"}</div>
                        <div>
                          <Text type="secondary" style={{ fontSize: "85%" }}>
                            关键词+语义模糊搜索
                          </Text>
                        </div>
                      </div>
                    ),
                  },
                  {
                    value: "none",
                    label: "关闭",
                  },
                  {
                    value: "team",
                    label: "术语百科",
                  },
                  {
                    value: "word",
                    label: "词义辨析",
                  },
                  {
                    value: "explain",
                    label: "经文解析",
                  },
                  {
                    value: "title",
                    label: "标题搜索",
                  },
                ]}
              />
            </Space>

            <AutoComplete
              style={{ flex: 1 }}
              placement="topLeft"
              value={inputValue}
              options={suggestions}
              onSelect={handleSelect}
              onChange={handleInputChange}
              notFoundContent={loading ? "搜索中..." : null}
              disabled={disabled}
            >
              <TextArea
                onKeyPress={handleKeyPress}
                placeholder={
                  placeholder || "提出你的问题，如：总结下面的内容..."
                }
                autoSize={{ minRows: 1, maxRows: 6 }}
                style={{ resize: "none", paddingRight: "48px" }}
              />
            </AutoComplete>
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
              <Dropdown
                placement="topLeft"
                menu={modelMenu}
                trigger={["click"]}
              >
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
