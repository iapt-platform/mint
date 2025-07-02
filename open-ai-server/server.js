const express = require("express");
const OpenAI = require("openai");
const cors = require("cors");

const app = express();
const PORT = process.env.PORT || 3000;

// 中间件
app.use(cors());
app.use(express.json());

// POST 路由处理OpenAI请求
app.post("/api/openai", async (req, res) => {
  try {
    const { open_ai_url, api_key, payload } = req.body;
    console.debug("request", open_ai_url);
    // 验证必需的参数
    if (!open_ai_url || !api_key || !payload) {
      return res.status(400).json({
        error: "Missing required parameters: open_ai_url, api_key, or payload",
      });
    }

    // 初始化OpenAI客户端
    const openai = new OpenAI({
      apiKey: api_key,
      baseURL: open_ai_url,
    });

    // 检查是否需要流式响应
    const isStreaming = payload.stream === true;

    if (isStreaming) {
      // 流式响应
      res.setHeader("Content-Type", "text/event-stream");
      res.setHeader("Cache-Control", "no-cache");
      res.setHeader("Connection", "keep-alive");
      res.setHeader("Access-Control-Allow-Origin", "*");

      try {
        const stream = await openai.chat.completions.create({
          ...payload,
          stream: true,
        });
        console.info("waiting response");
        for await (const chunk of stream) {
          const data = JSON.stringify(chunk);
          res.write(`data: ${data}\n\n`);
        }

        res.write("data: [DONE]\n\n");
        res.end();
      } catch (streamError) {
        console.error("Streaming error:", streamError);
        res.write(
          `data: ${JSON.stringify({ error: streamError.message })}\n\n`
        );
        res.end();
      }
    } else {
      // 非流式响应
      const completion = await openai.chat.completions.create(payload);

      res.json({
        success: true,
        data: completion,
      });
    }
  } catch (error) {
    console.error("API Error:", error);

    // 处理不同类型的错误
    if (error.status) {
      return res.status(error.status).json({
        error: error.message,
        type: error.type || "api_error",
      });
    }

    res.status(500).json({
      error: "Internal server error",
      message: error.message,
    });
  }
});

// 健康检查端点
app.get("/health", (req, res) => {
  res.json({ status: "OK", timestamp: new Date().toISOString() });
});

// 启动服务器
app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
  console.log(`Health check: http://localhost:${PORT}/health`);
  console.log(`API endpoint: http://localhost:${PORT}/api/openai`);
});

module.exports = app;
