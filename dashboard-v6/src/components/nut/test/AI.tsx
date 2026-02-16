import { Button, Input } from "antd";
import { useState } from "react";
interface IOpenAi {
  model: string;
  messages: Message[];
  prompt: string;
  temperature: number;
  stream: boolean;
}

interface Message {
  role: string;
  content: string;
}
const AI = () => {
  const [url, setUrl] = useState(
    "https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions"
  );
  const [key, setKey] = useState("");
  const [model, setModel] = useState("qwen-max-latest");
  const [concurrent, setConcurrent] = useState(10);
  return (
    <>
      <Input
        placeholder="url"
        value={url}
        onChange={(event) => {
          setUrl(event.target.value);
        }}
      />
      <Input
        placeholder="key"
        value={key}
        onChange={(event) => {
          setKey(event.target.value);
        }}
      />
      <Input
        placeholder="model"
        value={model}
        onChange={(event) => {
          setModel(event.target.value);
        }}
      />
      <Input
        placeholder="并发请求数量"
        value={concurrent}
        onChange={(event) => {
          setConcurrent(parseInt(event.target.value));
        }}
      />
      <Button
        onClick={() => {
          console.info("model", model);
          // 模拟请求数据，这里假设是向模型发送的文本等请求内容，实际按接口要求调整
          const requests: IOpenAi[] = Array.from(
            { length: concurrent },
            (_, i) => ({
              model: model,
              messages: [
                {
                  role: "system",
                  content: "you are translate assistant",
                },
                {
                  role: "user",
                  content:
                    "巴利文：‘‘ Sammā mānābhisamayā antamakāsi dukkhassā ’’ tiādīsu ( a . ni .7.9) pahānaṃ .\n中文译文：\n“通过正确（sammā）地觉悟（mānābhisamayā），他终结了（antamakāsi）痛苦（dukkhassa）”等（iti ādīsu）句子中，放弃（pahānaṃ）的含义被阐明。\n请从不同角度分析和评价译文，指出翻译错误，并给出建议的译文。\n",
                },
              ],
              prompt:
                "巴利文：‘‘ Sammā mānābhisamayā antamakāsi dukkhassā ’’ tiādīsu ( a . ni .7.9) pahānaṃ .\n中文译文：\n“通过正确（sammā）地觉悟（mānābhisamayā），他终结了（antamakāsi）痛苦（dukkhassa）”等（iti ādīsu）句子中，放弃（pahānaṃ）的含义被阐明。\n请从不同角度分析和评价译文，指出翻译错误，并给出建议的译文。\n",
              temperature: 0.7,
              stream: false,
            })
          );

          // 定义一个函数来发送单个 POST 请求
          const sendRequest = async (request: IOpenAi) => {
            try {
              console.info("ai post");
              const response = await fetch(url, {
                method: "POST",
                headers: {
                  "Content-Type": "application/json",
                  Authorization: `Bearer ${key}`,
                },
                body: JSON.stringify(request),
              });

              if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
              }

              const result = await response.json();
              return { status: "fulfilled", value: result };
            } catch (error) {
              return { status: "rejected", reason: error };
            }
          };

          // 并发执行 n 个请求
          (async () => {
            const results = await Promise.allSettled(requests.map(sendRequest));
            const successfulResults = results.filter(
              (result) =>
                result.status === "fulfilled" &&
                result.value.status === "fulfilled"
            );
            const failedResults = results.filter(
              (result) =>
                result.status === "fulfilled" &&
                result.value.status === "rejected"
            );

            console.log("Successful requests:", successfulResults);
            console.log("Failed requests:", failedResults);
          })();
        }}
      >
        AI run
      </Button>
    </>
  );
};
export default AI;
