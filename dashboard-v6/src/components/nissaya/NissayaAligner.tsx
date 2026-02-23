import { useEffect, useState } from "react";
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
import type { UploadChangeParam, UploadFile } from "antd/es/upload";
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

const { Dragger } = Upload;
const { TextArea } = Input;
const { Title } = Typography;

/* ------------------ 类型定义 ------------------ */

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

/* ------------------ 主组件 ------------------ */

const NissayaAligner = ({ sentencesId }: IWidget) => {
  const [current, setCurrent] = useState<number>(0);
  const [csvData, setCsvData] = useState<WordData[]>([]);
  const [jsonlInput, setJsonlInput] = useState<string>("");
  const [alignResults, setAlignResults] = useState<AlignResult[]>([]);
  const [original, setOriginal] = useState<SentenceData[]>([]);

  /* ------------------ 获取句子 ------------------ */

  useEffect(() => {
    if (!sentencesId) return;

    post<ISentenceDiffRequest, ISentenceDiffResponse>("/v2/sent-in-channel", {
      sentences: sentencesId,
      channels: ["_System_Pali_VRI_"],
    }).then((json) => {
      if (!json.ok) return;

      const rows = [...json.data.rows].sort((a, b) => {
        if (a.book_id !== b.book_id) return a.book_id - b.book_id;
        if (a.paragraph !== b.paragraph) return a.paragraph - b.paragraph;
        return a.word_start - b.word_start;
      });

      setOriginal(
        rows.map((item) => ({
          id: `${item.book_id}-${item.paragraph}-${item.word_start}-${item.word_end}`,
          content: item.content ?? "",
        }))
      );
    });
  }, [sentencesId]);

  /* ------------------ CSV 上传 ------------------ */

  const handleUpload = (info: UploadChangeParam<UploadFile>) => {
    const file = info.file.originFileObj;
    if (!file) {
      message.error("未检测到文件");
      return;
    }

    const reader = new FileReader();

    reader.onload = (e) => {
      const text = String(e.target?.result ?? "");
      parseCSV(text);
    };

    reader.onerror = () => message.error("读取文件失败");

    reader.readAsText(file, "utf-8");
  };

  /* ------------------ CSV 解析 ------------------ */

  const parseCSV = (text: string) => {
    const delimiter = text.includes("\t") ? "\t" : ",";
    const lines = text.trim().split(/\r?\n/);

    if (lines.length === 0) return;

    const headers = lines[0]
      .split(delimiter)
      .map((h) => h.replace(/"/g, "").trim().toLowerCase());

    const findIndex = (key: string) =>
      headers.findIndex((h) => h.includes(key));

    const paliIndex = findIndex("pali");
    const nissayaIndex = findIndex("nissaya");
    const noteIndex = findIndex("note");

    const data: WordData[] = lines.slice(1).map((line, i) => {
      const cols = line.split(delimiter).map((c) => c.replace(/"/g, "").trim());

      return {
        id: i + 1,
        pali: cols[paliIndex] ?? "",
        nissaya: cols[nissayaIndex] ?? "",
        note: cols[noteIndex] ?? "",
      };
    });

    setCsvData(data);
    message.success(`CSV 解析成功，共 ${data.length} 行`);
  };

  /* ------------------ Prompt 生成 ------------------ */

  const generatePrompt = (): string => {
    const sentenceJsonl = original
      .map((s) => `{"id":"${s.id}","content":"${s.content}"}`)
      .join("\n");

    const csvText = ["id,pali,nissaya,note"]
      .concat(
        csvData.map(
          (r) => `${r.id},"${r.pali}","${r.nissaya}","${r.note ?? ""}"`
        )
      )
      .join("\n");

    return `# 句子数据
\`\`\`jsonl
${sentenceJsonl}
\`\`\`

# 逐词解析数据
\`\`\`csv
${csvText}
\`\`\`

将逐词解析数据与句子对应，一个句子对多个逐词解析数据。
保持顺序且不可遗漏。
输出 jsonl 格式：
字段 id content words
words 为逐词 id，用逗号分隔`;
  };

  /* ------------------ JSONL 解析 ------------------ */

  const parseJsonlResults = () => {
    try {
      const results: AlignResult[] = jsonlInput
        .trim()
        .split(/\r?\n/)
        .map((line) => JSON.parse(line) as AlignResult);

      setAlignResults(results);
      message.success("解析成功");
      setCurrent(3);
    } catch {
      message.error("JSONL 格式错误");
    }
  };

  /* ------------------ 移动词 ------------------ */

  const moveWord = (sentenceIndex: number, direction: "prev" | "next") => {
    const targetIndex =
      direction === "prev" ? sentenceIndex - 1 : sentenceIndex + 1;

    if (targetIndex < 0 || targetIndex >= alignResults.length) return;

    const newResults = [...alignResults];

    const currentWords = newResults[sentenceIndex].words
      .split(",")
      .filter(Boolean);

    const movingWord =
      direction === "prev" ? currentWords.shift() : currentWords.pop();

    if (!movingWord) return;

    const targetWords = newResults[targetIndex].words
      .split(",")
      .filter(Boolean);

    if (direction === "prev") {
      targetWords.push(movingWord);
    } else {
      targetWords.unshift(movingWord);
    }

    newResults[sentenceIndex].words = currentWords.join(",");
    newResults[targetIndex].words = targetWords.join(",");

    setAlignResults(newResults);
  };

  /* ------------------ Steps 配置 ------------------ */

  const stepItems = [
    { title: "上传 CSV" },
    { title: "生成提示词" },
    { title: "粘贴 LLM 结果" },
    { title: "对齐预览" },
  ];

  /* ------------------ 内容页面 ------------------ */

  const stepContents = [
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
        <p>点击或拖拽上传 CSV 文件</p>
      </Dragger>

      {csvData.length > 0 && (
        <Table<WordData>
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
    </>,

    <>
      <Title level={5}>生成提示词</Title>
      <TextArea rows={20} value={generatePrompt()} readOnly />
      <Button
        icon={<CopyOutlined />}
        onClick={() => {
          navigator.clipboard.writeText(generatePrompt());
          message.success("已复制");
        }}
      >
        复制提示词
      </Button>
    </>,

    <>
      <TextArea
        rows={12}
        placeholder="粘贴 JSONL"
        value={jsonlInput}
        onChange={(e) => setJsonlInput(e.target.value)}
      />
      <Button type="primary" onClick={parseJsonlResults}>
        解析
      </Button>
    </>,

    <>
      {alignResults.map((res, idx) => {
        const sentence = original.find((s) => s.id === res.id);

        const wordList = res.words
          .split(",")
          .map(Number)
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
                    {isLast && <DownSquareOutlined style={{ marginLeft: 4 }} />}
                  </Button>
                );
              })}
            </Space>
          </div>
        );
      })}
    </>,
  ];

  /* ------------------ render ------------------ */

  return (
    <div style={{ padding: 24 }}>
      <Steps current={current} items={stepItems} />

      <div style={{ marginTop: 24 }}>{stepContents[current]}</div>

      <div style={{ marginTop: 24 }}>
        {current > 0 && (
          <Button onClick={() => setCurrent(current - 1)}>上一步</Button>
        )}

        {current < stepItems.length - 1 && (
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
