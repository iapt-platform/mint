import _React, { useEffect, useState } from "react";
import {
  Steps,
  Upload,
  Button,
  Table,
  Input,
  message,
  Typography,
  Space,
} from "antd";
import type { UploadChangeParam, UploadFile } from "antd/es/upload/interface";
import {
  InboxOutlined,
  CopyOutlined,
  UpSquareOutlined,
  DownSquareOutlined,
} from "@ant-design/icons";
import { post } from "../../request";
import type {
  ISentenceDiffRequest,
  ISentenceDiffResponse,
} from "../../api/Corpus";

const { Step } = Steps;
const { Dragger } = Upload;
const { TextArea } = Input;
const { Title } = Typography;

interface WordData {
  id: number;
  pali: string;
  nissaya: string;
  note?: string;
}

interface SentenceData {
  id: string;
  content: string;
}

interface AlignResult {
  id: string;
  words: string;
}

interface IWidget {
  sentencesId?: string[];
}

const NissayaAligner = ({ sentencesId }: IWidget) => {
  const [current, setCurrent] = useState<number>(0);
  const [csvData, setCsvData] = useState<WordData[]>([]);
  const [jsonlInput, setJsonlInput] = useState<string>("");
  const [alignResults, setAlignResults] = useState<AlignResult[]>([]);
  const [original, setOriginal] = useState<SentenceData[]>([]);

  useEffect(() => {
    if (sentencesId) {
      post<ISentenceDiffRequest, ISentenceDiffResponse>(`/v2/sent-in-channel`, {
        sentences: sentencesId,
        channels: ["_System_Pali_VRI_"],
      }).then((json) => {
        if (json.ok) {
          setOriginal(
            json.data.rows
              .sort((a, b) => {
                if (a.book_id !== b.book_id) {
                  return a.book_id - b.book_id;
                }
                if (a.paragraph !== b.paragraph) {
                  return a.paragraph - b.paragraph;
                }
                return a.word_start - b.word_start;
              })
              .map((item) => {
                return {
                  id: `${item.book_id}-${item.paragraph}-${item.word_start}-${item.word_end}`,
                  content: item.content ?? "",
                };
              })
          );
        }
      });
    }
  }, [sentencesId]);

  const handleUpload = (info: UploadChangeParam<UploadFile<any>>) => {
    console.log("Upload change event:", info);
    const file = info.fileList?.[0]?.originFileObj || info.file.originFileObj;
    if (!file) {
      console.error("No valid file found in upload event:", info);
      message.error("未检测到文件，请重新选择");
      return;
    }
    console.log("Selected file:", file.name, file);
    const reader = new FileReader();
    reader.onload = (e) => {
      const text = e.target?.result as string;
      console.log("✅ File read complete. Length:", text?.length || 0);
      parseCSV(text);
    };
    reader.onerror = (err) => {
      console.error("❌ File read error:", err);
      message.error("读取文件失败");
    };
    console.log("📖 Start reading file as text...");
    reader.readAsText(file as Blob, "utf-8");
  };

  const parseCSV = (text: string) => {
    console.log(
      "Parsing CSV... Raw preview (first 300 chars):",
      text.slice(0, 300)
    );
    const delimiter = text.includes("\t") ? "\t" : ",";
    console.log("Detected delimiter:", delimiter === "\t" ? "TAB" : "COMMA");
    const lines = text.trim().split(/\r?\n/);
    const headers = lines[0]
      .split(delimiter)
      .map((h) => h.replace(/\"/g, "").trim());
    console.log("Detected headers:", headers);

    const data: WordData[] = lines.slice(1).map((line, i) => {
      const cols = line
        .split(delimiter)
        .map((c) => c.replace(/\"/g, "").trim());
      return {
        id: i + 1,
        pali:
          cols[headers.findIndex((h) => h.toLowerCase().includes("pali"))] ||
          "",
        nissaya:
          cols[headers.findIndex((h) => h.toLowerCase().includes("nissaya"))] ||
          "",
        note:
          cols[headers.findIndex((h) => h.toLowerCase().includes("note"))] ||
          "",
      };
    });

    console.log("✅ Parsed CSV rows count:", data.length);
    console.table(data.slice(0, 5));
    setCsvData(data);
    message.success(`CSV 文件解析成功，共 ${data.length} 行`);
  };

  const generatePrompt = (): string => {
    const sentenceJsonl = original
      .map((s) => `{"id": "${s.id}", "content": "${s.content}"}`)
      .join("\n");
    const csvText = ["id,pali,nissaya,note"]
      .concat(
        csvData.map(
          (r) => `${r.id},"${r.pali}","${r.nissaya}","${r.note || ""}"`
        )
      )
      .join("\n");
    const prompt =
      "将逐词解析数据与句子对应，一个句子对多个逐词解析数据。不是每个单词都有逐词解析数据，保持逐词解析数据的顺序不变，不可以有遗漏。对齐结果jsonl格式，每行一个句子，每个句子有三个字段 ， id, content,words. 前两个字段与句子数据相同。逐词解析数据放在words字段中 , words字段里的数据为逐词解析数据的id字段，多个单词之间用逗号隔开";
    return `# 句子数据\n\n\n\`\`\`jsonl\n${sentenceJsonl}\n\`\`\`\n\n# 逐词解析数据\n\n\`\`\`csv\n${csvText}\n\`\`\`\n\n${prompt}`;
  };

  const parseJsonlResults = () => {
    try {
      console.log("Parsing JSONL input...");
      const lines = jsonlInput.trim().split(/\r?\n/);
      const results = lines.map((line) => JSON.parse(line)) as AlignResult[];
      console.log("Parsed results:", results);
      setAlignResults(results);
      message.success("结果解析成功");
      setCurrent(3);
    } catch (err) {
      console.error("❌ JSONL parse error:", err);
      message.error("JSONL 格式错误");
    }
  };

  const moveWord = (sentenceIndex: number, direction: "prev" | "next") => {
    console.log(
      `Moving word: sentenceIndex=${sentenceIndex}, direction=${direction}`
    );
    const targetIndex =
      direction === "prev" ? sentenceIndex - 1 : sentenceIndex + 1;
    if (targetIndex < 0 || targetIndex >= alignResults.length) return;

    const newResults = [...alignResults];
    const currentWords = newResults[sentenceIndex].words.split(",");
    const movingWord =
      direction === "prev" ? currentWords.shift() : currentWords.pop();
    if (!movingWord) return;

    const targetWords = newResults[targetIndex].words.split(",");
    if (direction === "prev") targetWords.push(movingWord);
    else targetWords.unshift(movingWord);

    newResults[sentenceIndex].words = currentWords.join(",");
    newResults[targetIndex].words = targetWords.join(",");

    console.log("Updated alignment:", newResults);
    setAlignResults(newResults);
  };

  const steps = [
    {
      title: "上传 CSV",
      content: (
        <>
          <Dragger
            accept=".csv,.tsv,.txt"
            showUploadList={false}
            beforeUpload={() => false}
            onChange={handleUpload}
          >
            <p className="ant-upload-drag-icon">
              <InboxOutlined />
            </p>
            <p className="ant-upload-text">点击或拖拽上传 CSV 文件</p>
          </Dragger>
          {csvData.length > 0 && (
            <Table
              dataSource={csvData}
              rowKey="id"
              pagination={{ pageSize: 50 }}
              scroll={{ y: 340 }}
              columns={[
                { title: "行号", dataIndex: "id", width: 120 },
                { title: "Pali", dataIndex: "pali", width: 420 },
                { title: "Nissaya", dataIndex: "nissaya" },
              ]}
            />
          )}
        </>
      ),
    },
    {
      title: "生成提示词",
      content: (
        <>
          <Title level={5}>生成的提示词：</Title>
          <TextArea rows={20} value={generatePrompt()} readOnly />
          <Button
            icon={<CopyOutlined />}
            onClick={() => {
              navigator.clipboard.writeText(generatePrompt());
              message.success("提示词已复制");
            }}
          >
            复制提示词
          </Button>
        </>
      ),
    },
    {
      title: "粘贴 LLM 结果",
      content: (
        <>
          <TextArea
            rows={12}
            placeholder="粘贴 LLM 输出的 JSONL 结果"
            value={jsonlInput}
            onChange={(e) => setJsonlInput(e.target.value)}
          />
          <Button type="primary" onClick={parseJsonlResults}>
            解析结果
          </Button>
        </>
      ),
    },
    {
      title: "对齐预览",
      content: (
        <>
          {alignResults.map((res, idx) => {
            const sentence = original.find((s) => s.id === res.id);
            const wordIds = res.words.split(",").map(Number);
            const wordList = wordIds
              .map((id) => csvData.find((d) => d.id === id))
              .filter(Boolean) as WordData[];

            return (
              <div key={res.id} style={{ marginBottom: 24 }}>
                <Title level={5}>
                  {res.id} — {sentence?.content}
                </Title>
                <Space wrap>
                  {wordList.map((w, i) => {
                    const isFirst = i === 0;
                    const isLast = i === wordList.length - 1;
                    return (
                      <Button
                        key={w.id}
                        type={isFirst || isLast ? "primary" : "default"}
                        icon={isFirst ? <UpSquareOutlined /> : undefined}
                        onClick={() => {
                          if (isFirst) moveWord(idx, "prev");
                          if (isLast) moveWord(idx, "next");
                        }}
                      >
                        {`${w.pali} (${w.nissaya})`}
                        {isLast && (
                          <DownSquareOutlined style={{ marginLeft: 4 }} />
                        )}
                      </Button>
                    );
                  })}
                </Space>
              </div>
            );
          })}
        </>
      ),
    },
  ];

  return (
    <div style={{ padding: 24 }}>
      <Steps current={current}>
        {steps.map((item) => (
          <Step key={item.title} title={item.title} />
        ))}
      </Steps>
      <div style={{ marginTop: 24 }}>{steps[current].content}</div>
      <div style={{ marginTop: 24 }}>
        {current > 0 && (
          <Button onClick={() => setCurrent(current - 1)}>上一步</Button>
        )}
        {current < steps.length - 1 && (
          <Button
            type="primary"
            style={{ marginLeft: 8 }}
            onClick={() => setCurrent(current + 1)}
          >
            下一步
          </Button>
        )}
      </div>
    </div>
  );
};

export default NissayaAligner;
