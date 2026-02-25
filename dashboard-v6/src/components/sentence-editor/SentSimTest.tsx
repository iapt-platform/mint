/**
 * SentSimTest — 可视化 Demo 页
 * 通过 fetcher prop 注入 mock 函数，无需真实后端即可调试 SentSimWidget 的所有交互。
 */
import { useCallback, useState } from "react";
import {
  Alert,
  Badge,
  Button,
  Card,
  Col,
  Divider,
  Form,
  InputNumber,
  Row,
  Select,
  Slider,
  Space,
  Tag,
  Typography,
} from "antd";

import type {
  ISentenceSimListResponse,
  ISentSimParams,
} from "../../api/sent-sim";
import SentSimWidget from "./SentSim";

const { Title } = Typography;

// ─── Mock 数据 ────────────────────────────────────────────────────────────────

const MOCK_SENTENCES = [
  { sent: "The quick brown fox jumps over the lazy dog.", sim: 1.0 },
  { sent: "A fast auburn fox leaps above the sleepy hound.", sim: 0.92 },
  { sent: "The nimble red fox vaults the tired dog.", sim: 0.85 },
  { sent: "A swift fox jumped across the resting canine.", sim: 0.78 },
  { sent: "The brown fox made a leap over the dog.", sim: 0.71 },
  { sent: "Foxes are known for their agility and speed.", sim: 0.55 },
  { sent: "Dogs tend to rest more than foxes in captivity.", sim: 0.42 },
  { sent: "The weather is pleasant today with mild winds.", sim: 0.21 },
  { sent: "She sells seashells by the seashore.", sim: 0.15 },
  { sent: "An entirely unrelated sentence about quantum physics.", sim: 0.05 },
];

// ─── Mock 模式 ────────────────────────────────────────────────────────────────

type MockMode = "success" | "slow" | "empty" | "error";

const MODE_OPTIONS: { value: MockMode; label: string; color: string }[] = [
  { value: "success", label: "✅ 正常返回", color: "green" },
  { value: "slow", label: "🐢 慢速 2s", color: "orange" },
  { value: "empty", label: "📭 空数据", color: "blue" },
  { value: "error", label: "❌ 服务器错误", color: "red" },
];

function sleep(ms: number) {
  return new Promise<void>((r) => setTimeout(r, ms));
}

// ─── Demo 配置 ────────────────────────────────────────────────────────────────

interface IDemoConfig {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  limit: number;
  delay: number;
  mode: MockMode;
}

const DEFAULT: IDemoConfig = {
  book: 1,
  para: 1,
  wordStart: 0,
  wordEnd: 5,
  limit: 3,
  delay: 400,
  mode: "success",
};

// ─── 组件 ─────────────────────────────────────────────────────────────────────

const SentSimTest = () => {
  const [draft, setDraft] = useState<IDemoConfig>(DEFAULT);
  const [applied, setApplied] = useState<IDemoConfig>(DEFAULT);
  // widgetKey 变化时强制销毁重建 Widget，确保状态干净
  const [widgetKey, setWidgetKey] = useState(0);

  const fetcher = useCallback(
    async (params: ISentSimParams): Promise<ISentenceSimListResponse> => {
      await sleep(applied.mode === "slow" ? 2000 : applied.delay);

      if (applied.mode === "error") {
        return {
          ok: false,
          message: "Mock 错误：服务器返回 500",
          data: { rows: [], count: 0 },
        };
      }

      if (applied.mode === "empty") {
        return { ok: true, message: "", data: { rows: [], count: 0 } };
      }

      const pool =
        params.sim === 1
          ? MOCK_SENTENCES.filter((s) => s.sim >= 0.9)
          : MOCK_SENTENCES;

      const rows = pool.slice(params.offset, params.offset + params.limit);
      return { ok: true, message: "", data: { rows, count: pool.length } };
    },
    [applied.mode, applied.delay]
  );

  function apply() {
    setApplied(draft);
    setWidgetKey((k) => k + 1);
  }

  function reset() {
    setDraft(DEFAULT);
    setApplied(DEFAULT);
    setWidgetKey((k) => k + 1);
  }

  const currentMode = MODE_OPTIONS.find((o) => o.value === applied.mode)!;

  return (
    <div style={{ padding: 24, maxWidth: 860, margin: "0 auto" }}>
      <Title level={3}>SentSimWidget · Mock 调试面板</Title>

      {/* ── 控制面板 ─────────────────────────────────────────────────────── */}
      <Card
        size="small"
        title="⚙️ 参数配置"
        style={{ marginBottom: 20 }}
        extra={<Badge color={currentMode.color} text={currentMode.label} />}
      >
        <Form layout="inline" size="small">
          <Row gutter={[12, 12]} style={{ width: "100%" }}>
            {/* 位置参数 */}
            {(["book", "para", "wordStart", "wordEnd", "limit"] as const).map(
              (key) => (
                <Col key={key} span={4}>
                  <Form.Item label={key}>
                    <InputNumber
                      min={0}
                      value={draft[key]}
                      onChange={(v) =>
                        setDraft((d) => ({ ...d, [key]: v ?? 0 }))
                      }
                      style={{ width: "100%" }}
                    />
                  </Form.Item>
                </Col>
              )
            )}

            {/* Mock 模式 */}
            <Col span={8}>
              <Form.Item label="Mock 模式">
                <Select
                  value={draft.mode}
                  style={{ width: 160 }}
                  options={MODE_OPTIONS.map(({ value, label }) => ({
                    value,
                    label,
                  }))}
                  onChange={(v) => setDraft((d) => ({ ...d, mode: v }))}
                />
              </Form.Item>
            </Col>

            {/* 延迟 */}
            <Col span={10}>
              <Form.Item label={`延迟 ${draft.delay} ms`}>
                <Slider
                  min={0}
                  max={3000}
                  step={100}
                  value={draft.delay}
                  disabled={draft.mode === "slow"}
                  style={{ width: 180 }}
                  onChange={(v) => setDraft((d) => ({ ...d, delay: v }))}
                />
              </Form.Item>
            </Col>

            {/* 操作按钮 */}
            <Col span={24} style={{ textAlign: "right" }}>
              <Space>
                <Button onClick={reset}>重置默认</Button>
                <Button type="primary" onClick={apply}>
                  应用 &amp; 重建 Widget
                </Button>
              </Space>
            </Col>
          </Row>
        </Form>
      </Card>

      {/* ── Mock 数据预览 ─────────────────────────────────────────────────── */}
      <Card
        size="small"
        title="📋 Mock 数据库（共 10 条）"
        style={{ marginBottom: 20 }}
      >
        <Space wrap size={[6, 6]}>
          {MOCK_SENTENCES.map((s, i) => (
            <Tag
              key={i}
              color={s.sim >= 0.9 ? "green" : s.sim >= 0.5 ? "blue" : "default"}
            >
              sim={s.sim.toFixed(2)} · {s.sent.slice(0, 28)}…
            </Tag>
          ))}
        </Space>
        <Alert
          style={{ marginTop: 10 }}
          type="info"
          showIcon
          message='开启"只显示相同句"时只返回 sim ≥ 0.9 的数据（前 2 条）'
        />
      </Card>

      <Divider>↓ SentSimWidget 实际渲染</Divider>

      {/* ── Widget ───────────────────────────────────────────────────────── */}
      <Card>
        <SentSimWidget
          key={widgetKey}
          book={applied.book}
          para={applied.para}
          wordStart={applied.wordStart}
          wordEnd={applied.wordEnd}
          limit={applied.limit}
          fetcher={fetcher}
          onCreate={() => console.log("[SentSimTest] onCreate")}
        />
      </Card>
    </div>
  );
};

export default SentSimTest;
