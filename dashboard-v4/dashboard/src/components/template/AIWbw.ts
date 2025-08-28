import { useState, useCallback } from "react";
import { IWbw } from "./Wbw/WbwWord";
import { paliEndingType } from "../general/PaliEnding";
import { useIntl } from "react-intl";

// 类型定义
export interface WbwElement<T> {
  value: T;
  status: number;
}

interface StreamController {
  addData: (jsonlLine: string) => void;
  complete: () => void;
}

interface ProcessWbwStreamOptions {
  modelId: string;
  prompt: string;
  endingType: string[];
  onProgress?: (data: IWbw[], isComplete: boolean) => void;
  onComplete?: (finalData: IWbw[]) => void;
  onError?: (error: string) => void;
}

/**
 * 处理JSONL流式输出的函数
 */
export const processWbwStream = async ({
  modelId,
  prompt,
  endingType,
  onProgress,
  onComplete,
  onError,
}: ProcessWbwStreamOptions): Promise<{
  success: boolean;
  data?: IWbw[];
  error?: string;
}> => {
  if (typeof process.env.REACT_APP_OPENAI_PROXY === "undefined") {
    console.error("no REACT_APP_OPENAI_PROXY");
    const error = "API配置错误";
    onError?.(error);
    return { success: false, error };
  }

  try {
    const payload = {
      model: "grok-3", // 或者从models数组中获取实际模型名称
      messages: [
        {
          role: "system",
          content: `你是一个巴利语专家，
        用户提供的json 数据 是巴利文句子的全部单词
          请根据每个单词的拼写 real.value 填写如下字段
单词的中文意思：meaning.value
巴利单词的拆分：factors.value  
语尾请加[]
拆分后每个组成部分的中文意思factorMeaning.value
请按照下表填写巴利语单词的类型 type.value
\`\`\`csv
${endingType.join("\n")}
\`\`\`
 直接输出JSONL格式数据
`,
        },
        { role: "user", content: prompt },
      ],
      stream: true,
      temperature: 0.3,
      max_tokens: 4000,
    };

    const url = process.env.REACT_APP_OPENAI_PROXY;
    const requestData = {
      model_id: modelId,
      payload: payload,
    };

    console.info("api request", url, requestData);

    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer AIzaSyCzr8KqEdaQ3cRCxsFwSHh8c7kF3RZTZWw`,
      },
      body: JSON.stringify(requestData),
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
    let jsonlBuffer = ""; // 用于累积JSONL内容
    const resultData: IWbw[] = [];

    // 创建流控制器
    const streamController: StreamController = {
      addData: (jsonlLine: string) => {
        try {
          // 解析JSONL行
          const parsedData = JSON.parse(jsonlLine.trim());
          console.info("stream ok", parsedData);
          // 转换为IWbw格式
          const wbwData: IWbw = {
            book: parsedData.book || 0,
            para: parsedData.para || 0,
            sn: parsedData.sn || [],
            word: parsedData.word || { value: "", status: 0 },
            real: parsedData.real || { value: null, status: 0 },
            meaning: parsedData.meaning,
            type: parsedData.type,
            grammar: parsedData.grammar,
            style: parsedData.style,
            case: parsedData.case,
            parent: parsedData.parent,
            parent2: parsedData.parent2,
            grammar2: parsedData.grammar2,
            factors: parsedData.factors,
            factorMeaning: parsedData.factorMeaning,
            relation: parsedData.relation,
            note: parsedData.note,
            bookMarkColor: parsedData.bookMarkColor,
            bookMarkText: parsedData.bookMarkText,
            locked: parsedData.locked || false,
            confidence: parsedData.confidence || 0.5,
            attachments: parsedData.attachments,
            hasComment: parsedData.hasComment,
            grammarId: parsedData.grammarId,
            bookName: parsedData.bookName,
            editor: parsedData.editor,
            created_at: parsedData.created_at,
            updated_at: parsedData.updated_at,
          };

          resultData.push(wbwData);

          // 调用进度回调
          onProgress?.(resultData, false);
        } catch (e) {
          console.warn("解析JSONL行失败:", e, "内容:", jsonlLine);
        }
      },
      complete: () => {
        onProgress?.(resultData, true);
        onComplete?.(resultData);
      },
    };

    try {
      while (true) {
        const { done, value } = await reader.read();

        if (done) {
          // 处理最后的缓冲内容
          if (jsonlBuffer.trim()) {
            const lines = jsonlBuffer.trim().split("\n");
            for (const line of lines) {
              if (line.trim()) {
                streamController.addData(line);
              }
            }
          }
          streamController.complete();
          return { success: true, data: resultData };
        }

        buffer += decoder.decode(value, { stream: true });
        const lines = buffer.split("\n");
        buffer = lines.pop() || "";

        for (const line of lines) {
          if (line.trim() === "") continue;

          if (line.startsWith("data: ")) {
            const data = line.slice(6);

            if (data === "[DONE]") {
              // 处理剩余的JSONL内容
              if (jsonlBuffer.trim()) {
                const jsonlLines = jsonlBuffer.trim().split("\n");
                for (const jsonlLine of jsonlLines) {
                  if (jsonlLine.trim()) {
                    streamController.addData(jsonlLine);
                  }
                }
              }
              streamController.complete();
              return { success: true, data: resultData };
            }

            try {
              const parsed = JSON.parse(data);
              const delta = parsed.choices?.[0]?.delta;

              if (delta?.content) {
                // 累积内容到JSONL缓冲区
                jsonlBuffer += delta.content;

                // 检查是否有完整的JSONL行
                const jsonlLines = jsonlBuffer.split("\n");

                // 保留最后一行（可能不完整）
                jsonlBuffer = jsonlLines.pop() || "";

                // 处理完整的行
                for (const jsonlLine of jsonlLines) {
                  if (jsonlLine.trim()) {
                    streamController.addData(jsonlLine);
                  }
                }
              }
            } catch (e) {
              console.warn("解析SSE数据失败:", e);
            }
          }
        }
      }
    } catch (error) {
      console.error("读取流数据失败:", error);
      const errorMessage = "读取响应流失败";
      onError?.(errorMessage);
      return { success: false, error: errorMessage };
    }
  } catch (error) {
    console.error("API调用失败:", error);
    const errorMessage = "API调用失败，请重试";
    onError?.(errorMessage);
    return { success: false, error: errorMessage };
  }
};

/**
 * React Hook 用法示例
 */
export const useWbwStreamProcessor = () => {
  const [isProcessing, setIsProcessing] = useState<boolean>(false);
  const [wbwData, setWbwData] = useState<IWbw[]>([]);
  const [error, setError] = useState<string>();

  const intl = useIntl(); // 在Hook中使用

  const endingType = paliEndingType.map((item) => {
    return (
      intl.formatMessage({ id: `dict.fields.type.${item}.label` }) +
      `:.${item}.`
    );
  });

  const processStream = useCallback(async (modelId: string, prompt: string) => {
    setIsProcessing(true);
    setWbwData([]);
    setError(undefined);

    const result = await processWbwStream({
      modelId,
      prompt,
      endingType,
      onProgress: (data, isComplete) => {
        console.info("onProgress", data);
        setWbwData([...data]); // 创建新数组触发重渲染
      },
      onComplete: (finalData) => {
        setWbwData(finalData);
        setIsProcessing(false);
      },
      onError: (errorMessage) => {
        setError(errorMessage);
        setIsProcessing(false);
      },
    });

    if (!result.success) {
      setError(result.error || "处理失败");
      setIsProcessing(false);
    }

    return result;
  }, []);

  return {
    processStream,
    isProcessing,
    wbwData,
    error,
    clearData: useCallback(() => {
      setWbwData([]);
      setError(undefined);
    }, []),
  };
};
