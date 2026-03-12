/**
 * SentEditInnerDemo.tsx
 * ──────────────────────────────────────────────────────────────────
 * 用于在页面上直观测试 <SentEditInner> 组件的演示页面。
 * 所有数据均为 mock，不依赖真实 API。
 *
 * 使用方式（开发环境临时路由）：
 *   <Route path="/dev/sent-edit-demo" element={<SentEditInnerDemo />} />
 * ──────────────────────────────────────────────────────────────────
 */

import React, { useState } from "react";
import { ConfigProvider, Space, Switch, Tag, Typography, Divider } from "antd";

import type { ISentence } from "../../api/sentence";
import type { ITocPathNode } from "../../api/pali-text";
import type { IUser } from "../../api/Auth";
import { SentEditInner, type IWidgetSentEditInner } from "./SentEdit";
import type { IWbw } from "../../types/wbw";

const { Title, Text } = Typography;

// ─── Mock 数据 ────────────────────────────────────────────────────

const mockEditor: IUser = {
  id: "user-001",
  avatar: "",
  nickName: "测试用户",
  userName: "测试用户",
};

const mockTranslationChannel = {
  id: "ch-translation-01",
  name: "汉译频道",
  type: "translation" as const,
  studio: { id: "studio-01", name: "测试工作室" },
};

const mockCommentaryChannel = {
  id: "ch-commentary-01",
  name: "注释频道",
  type: "commentary" as const,
  studio: { id: "studio-01", name: "测试工作室" },
};

/** 原文（json 格式的 wbw 数据 + html） */

const mockOrigin: ISentence[] = [
  {
    id: "origin-001",
    book: 1,
    para: 1,
    wordStart: 1,
    wordEnd: 5,
    content: JSON.stringify([
      {
        uid: "w1",
        book: 1,
        para: 1,
        sn: [1],
        word: { value: "Evaṃ", status: 7 },
        real: { value: "evaṃ", status: 7 },
        meaning: { value: "如是", status: 7 },
        type: { value: "ind", status: 7 },
        grammar: { value: "不变词", status: 7 },
        confidence: 1,
      },
      {
        uid: "w2",
        book: 1,
        para: 1,
        sn: [2],
        word: { value: "me", status: 7 },
        real: { value: "ahaṃ", status: 7 },
        meaning: { value: "我", status: 7 },
        type: { value: "pron", status: 7 },
        grammar: { value: "代词 主格 单数", status: 7 },
        confidence: 1,
      },
      {
        uid: "w3",
        book: 1,
        para: 1,
        sn: [3],
        word: { value: "sutaṃ", status: 7 },
        real: { value: "suta", status: 7 },
        meaning: { value: "所闻", status: 7 },
        type: { value: "pp", status: 7 },
        grammar: { value: "过去分词 主格 单数 中性", status: 7 },
        confidence: 1,
      },
      {
        uid: "w4",
        book: 1,
        para: 1,
        sn: [4],
        word: { value: "—", status: 0 },
        real: { value: null, status: 0 },
        meaning: { value: "——", status: 0 },
        confidence: 0,
      },
      {
        uid: "w5",
        book: 1,
        para: 1,
        sn: [5],
        word: { value: "ekaṃ", status: 7 },
        real: { value: "eka", status: 7 },
        meaning: { value: "一", status: 7 },
        type: { value: "num", status: 7 },
        grammar: { value: "数词 业格 单数 中性", status: 7 },
        confidence: 1,
      },
    ] satisfies IWbw[]),
    contentType: "json",
    html: "<span>Evaṃ me sutaṃ — ekaṃ</span>",
    editor: mockEditor,
    channel: { id: "origin", name: "原文", type: "translation" as const },
    updateAt: "2024-01-01T00:00:00Z",
  },
  {
    id: "origin-002",
    book: 1,
    para: 1,
    wordStart: 1,
    wordEnd: 5,
    content: "Evaṃ me sutaṃ — ekaṃ",
    contentType: "markdown",
    html: "<span>Evaṃ me sutaṃ — ekaṃ</span>",
    editor: mockEditor,
    channel: {
      id: "origin-text",
      name: "原文(文本)",
      type: "translation" as const,
    },
    updateAt: "2024-01-01T00:00:00Z",
  },
];

/** 翻译列表（translation + nissaya + commentary） */
const mockTranslation: ISentence[] = [
  {
    id: "trans-001",
    book: 1,
    para: 1,
    wordStart: 1,
    wordEnd: 5,
    content: "如是我闻——一时，",
    contentType: "markdown",
    html: "<p>如是我闻——一时，</p>",
    editor: mockEditor,
    channel: mockTranslationChannel,
    updateAt: "2024-03-01T08:00:00Z",
    suggestionCount: { suggestion: 3, discussion: 1 },
  },
  {
    id: "nissaya-001",
    book: 1,
    para: 1,
    wordStart: 1,
    wordEnd: 5,
    content: "（evaṃ）如是（me）我（sutaṃ）所闻",
    contentType: "markdown",
    html: "<p>（evaṃ）如是（me）我（sutaṃ）所闻</p>",
    editor: mockEditor,
    channel: mockTranslationChannel,
    updateAt: "2024-03-02T09:00:00Z",
  },
];

/** 注释列表（单独卡片展示） */
const mockCommentaries: ISentence[] = [
  {
    id: "comm-001",
    book: 1,
    para: 1,
    wordStart: 1,
    wordEnd: 5,
    content:
      "「如是我闻」是结集者阿难在结集时所加的导语，表明以下内容是亲耳所闻。",
    contentType: "markdown",
    html: "<p>「如是我闻」是结集者阿难在结集时所加的导语，表明以下内容是亲耳所闻。</p>",
    editor: mockEditor,
    channel: mockCommentaryChannel,
    updateAt: "2024-03-03T10:00:00Z",
    openInEditMode: false,
  },
];

/** 目录路径 */
const mockPath: ITocPathNode[] = [
  { title: "长部", paliTitle: "Dīgha Nikāya", level: 0, book: 1 },
  {
    title: "梵网经",
    paliTitle: "Brahmajāla Sutta",
    level: 1,
    book: 1,
    paragraph: 1,
  },
];

// ─── 基础 props ────────────────────────────────────────────────────

const baseProps: IWidgetSentEditInner = {
  id: "sent-demo-1_1_1_5",
  book: 1,
  para: 1,
  wordStart: 1,
  wordEnd: 5,
  origin: mockOrigin,
  translation: mockTranslation,
  commentaries: mockCommentaries,
  path: mockPath,
  tranNum: 2,
  nissayaNum: 1,
  commNum: 1,
  originNum: 1,
  simNum: 0,
  compact: false,
  showWbwProgress: false,
  readonly: false,
};

// ─── Demo 页面 ────────────────────────────────────────────────────

const SentEditInnerDemo: React.FC = () => {
  const [layout, setLayout] = useState<"column" | "row">("column");
  const [compact, setCompact] = useState(false);
  const [readonly, setReadonly] = useState(false);
  const [showWbwProgress, setShowWbwProgress] = useState(false);

  return (
    <ConfigProvider>
      <div style={{ maxWidth: 1200, margin: "0 auto", padding: "24px 16px" }}>
        {/* 页头 */}
        <div style={{ marginBottom: 24 }}>
          <Title level={3} style={{ margin: 0 }}>
            🧪 SentEditInner — 组件演示
          </Title>
          <Text type="secondary">
            book=1 · para=1 · wordStart=1 · wordEnd=5 · 所有数据为 mock
          </Text>
        </div>

        {/* 控制面板 */}
        <div
          style={{
            display: "flex",
            flexWrap: "wrap",
            gap: 24,
            padding: "16px 20px",
            background: "#fafafa",
            border: "1px solid #e8e8e8",
            borderRadius: 8,
            marginBottom: 32,
          }}
        >
          <Space>
            <Text>layout</Text>
            <Switch
              checkedChildren="row"
              unCheckedChildren="column"
              checked={layout === "row"}
              onChange={(v) => setLayout(v ? "row" : "column")}
            />
            <Tag color={layout === "row" ? "blue" : "green"}>{layout}</Tag>
          </Space>
          <Space>
            <Text>compact</Text>
            <Switch checked={compact} onChange={setCompact} />
          </Space>
          <Space>
            <Text>readonly</Text>
            <Switch checked={readonly} onChange={setReadonly} />
          </Space>
          <Space>
            <Text>showWbwProgress</Text>
            <Switch checked={showWbwProgress} onChange={setShowWbwProgress} />
          </Space>
        </div>

        <Divider>渲染结果</Divider>

        {/* 目标组件 */}
        <div
          style={{
            border: "2px dashed #d9d9d9",
            borderRadius: 8,
            padding: 16,
            background: "#fff",
          }}
        >
          <SentEditInner
            {...baseProps}
            layout={layout}
            compact={compact}
            readonly={readonly}
            showWbwProgress={showWbwProgress}
            onTranslationChange={(data) => {
              console.log("[Demo] onTranslationChange →", data);
            }}
          />
        </div>

        {/* 第二个：无注释、无翻译的极简情形 */}
        <Divider style={{ marginTop: 40 }}>极简情形（无翻译 / 无注释）</Divider>
        <div
          style={{
            border: "2px dashed #ffe58f",
            borderRadius: 8,
            padding: 16,
            background: "#fffbe6",
          }}
        >
          <SentEditInner
            id="sent-demo-minimal"
            book={1}
            para={2}
            wordStart={1}
            wordEnd={3}
            origin={[mockOrigin[1]]}
            originNum={1}
            layout="column"
            compact
            readonly
          />
        </div>

        {/* Mock 数据预览 */}
        <Divider style={{ marginTop: 40 }}>Mock 数据预览</Divider>
        <pre
          style={{
            background: "#282c34",
            color: "#abb2bf",
            padding: 20,
            borderRadius: 8,
            fontSize: 12,
            overflow: "auto",
            maxHeight: 400,
          }}
        >
          {JSON.stringify(
            {
              origin: mockOrigin,
              translation: mockTranslation,
              commentaries: mockCommentaries,
              path: mockPath,
            },
            null,
            2
          )}
        </pre>
      </div>
    </ConfigProvider>
  );
};

export default SentEditInnerDemo;
