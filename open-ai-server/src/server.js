import express from "express";
import OpenAI from "openai";
import cors from "cors";

import logger from "./logger";
import config from "./config";

const app = express();

// 中间件
app.use(cors());
app.use(express.json());
const api_server = config["api_server"];
// POST 路由处理OpenAI请求
app.post("/api/openai", async (req, res) => {
  try {
    const { model_id, open_ai_url, api_key, payload } = req.body;

    let requestUrl = open_ai_url;
    let apiKey = api_key;
    // 验证必需的参数
    if (!model_id) {
      if (!open_ai_url || !api_key || !payload) {
        return res.status(400).json({
          error:
            "Missing required parameters: open_ai_url, api_key, or payload",
        });
      }
    } else {
      //get model info from api server
      try {
        const url = api_server + `/v2/ai-model/${model_id}`;
        logger.info("get model info from api server " + url);
        const response = await fetch(url, {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
        });
        // 获取响应数据
        const model = await response.json();
        if (model.ok) {
          requestUrl = model.data.url;
          apiKey = model.data.key;
        } else {
          return res.status(400).json({
            error:
              "Missing required parameters: open_ai_url, api_key, or payload",
          });
        }
      } catch (error) {
        logger.error(error.message);
        if (!res.headersSent) {
          res.status(500).json({
            error: "Proxy server error",
            message: error.message,
            type: "proxy_error",
          });
        }
      }
    }

    // 检测不同的 AI 服务提供商

    const isClaudeAPI =
      requestUrl.includes("anthropic.com") || requestUrl.includes("claude");

    const isStreaming = payload.stream === true;

    // 构建请求URL和headers

    let headers = {
      "Content-Type": "application/json",
      Authorization: `Bearer ${apiKey}`,
    };

    if (isClaudeAPI) {
      // Claude API使用特殊的header格式
      headers["x-api-key"] = apiKey;
      headers["anthropic-version"] = "2023-06-01";
    }

    logger.info("request " + requestUrl);
    if (isStreaming) {
      // 流式响应处理
      res.setHeader("Content-Type", "text/event-stream");
      res.setHeader("Cache-Control", "no-cache");
      res.setHeader("Connection", "keep-alive");
      res.setHeader("Access-Control-Allow-Origin", "*");

      try {
        const response = await fetch(requestUrl, {
          method: "POST",
          headers: headers,
          body: JSON.stringify(payload),
        });

        // 复制响应头到客户端
        response.headers.forEach((value, key) => {
          // 跳过一些不需要的头部
          if (
            ![
              "content-encoding",
              "content-length",
              "transfer-encoding",
            ].includes(key.toLowerCase())
          ) {
            res.setHeader(key, value);
          }
        });

        // 设置响应状态码（直接使用大模型返回的状态码）
        res.status(response.status);

        if (!response.ok) {
          // 对于错误响应，也要透传原始数据
          const reader = response.body.getReader();
          const decoder = new TextDecoder();

          while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            const chunk = decoder.decode(value, { stream: true });
            res.write(chunk);
          }
          res.end();
          return;
        }

        // 处理成功的流式响应
        const reader = response.body.getReader();
        const decoder = new TextDecoder();

        while (true) {
          const { done, value } = await reader.read();
          if (done) break;

          const chunk = decoder.decode(value, { stream: true });
          res.write(chunk);
        }

        res.end();
      } catch (streamError) {
        logger.error("Streaming error:" + streamError.message);

        // 网络错误或其他系统错误
        res.status(500);
        res.setHeader("Content-Type", "application/json");
        res.json({
          error: "Proxy server error",
          message: streamError.message,
          type: "proxy_error",
        });
      }
    } else {
      // 非流式响应处理
      const response = await fetch(requestUrl, {
        method: "POST",
        headers: headers,
        body: JSON.stringify(payload),
      });

      // 复制响应头到客户端
      response.headers.forEach((value, key) => {
        // 跳过一些不需要的头部
        if (
          !["content-encoding", "content-length", "transfer-encoding"].includes(
            key.toLowerCase()
          )
        ) {
          res.setHeader(key, value);
        }
      });

      // 设置响应状态码（直接使用大模型返回的状态码）
      res.status(response.status);

      // 获取响应数据
      const responseData = await response.json();

      // 直接返回原始响应数据，不进行任何修改
      res.json(responseData);
    }
  } catch (error) {
    logger.error("Proxy Error:" + error.message);

    // 只有在系统级错误时才返回代理服务器的错误信息
    // 比如网络错误、JSON解析错误等
    if (!res.headersSent) {
      res.status(500).json({
        error: "Proxy server error",
        message: error.message,
        type: "proxy_error",
      });
    }
  }
});

// 健康检查端点
app.get("/health", (req, res) => {
  res.json({ status: "OK", timestamp: new Date().toISOString() });
});

export default app;
