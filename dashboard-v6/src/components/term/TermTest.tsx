import React, { useState } from "react";
import {
  Badge,
  Button,
  Card,
  Col,
  Row,
  Space,
  Switch,
  Table,
  Tag,
  Typography,
} from "antd";
import {
  BookOutlined,
  EditOutlined,
  GlobalOutlined,
  PlusOutlined,
} from "@ant-design/icons";

// ─── 导入被测试的真实组件 ──────────────────────────────────────────────────────
import TermModal from "./TermModal";
import TermEdit from "./TermEdit";
import type { ITermDataResponse } from "../../api/Term";
import type { IChannel } from "../../api/channel";

const { Title, Text, Paragraph } = Typography;

// ─── Mock 数据 ─────────────────────────────────────────────────────────────────

const mockStudio = {
  id: "studio-001",
  nickName: "语言工作室",
  studioName: "lang-studio",
  realName: "Language Studio",
  avatar: "https://api.dicebear.com/7.x/bottts/svg?seed=studio",
  roles: ["owner"],
};

const mockChannel: IChannel = {
  id: "channel-001",
  name: "英语词汇",
  type: "translation",
  lang: "en",
};

const mockEditor = {
  id: "user-001",
  nickName: "张三",
  userName: "zhangsan",
  roles: ["editor"],
};

const MOCK_TERMS: ITermDataResponse[] = [
  {
    id: 1,
    guid: "term-guid-001",
    word: "ephemeral",
    tag: "adjective",
    meaning: "短暂的；转瞬即逝的",
    other_meaning: "瞬间的，昙花一现的",
    note: "常用于描述事物的短暂存在，如 ephemeral beauty（短暂的美丽）",
    html: "<p>短暂的；转瞬即逝的</p>",
    channal: "channel-001",
    channel: mockChannel,
    studio: mockStudio,
    editor: mockEditor,
    role: "editor",
    exp: 85,
    language: "en",
    community: false,
    summary: "形容某事物存在时间极短，很快就消逝的状态。",
    summary_is_community: false,
    created_at: "2024-01-15T08:30:00Z",
    updated_at: "2024-03-10T14:22:00Z",
  },
  {
    id: 2,
    guid: "term-guid-002",
    word: "serendipity",
    tag: "noun",
    meaning: "意外发现美好事物的运气；机缘巧合",
    other_meaning: "奇缘，幸运的意外发现",
    note: "源自波斯童话《三个锡兰王子》，常用于形容偶然发现好东西的惊喜",
    channal: "channel-001",
    channel: mockChannel,
    studio: mockStudio,
    editor: mockEditor,
    role: "editor",
    exp: 92,
    language: "en",
    community: true,
    summary: "指在寻找某物时意外发现更有价值事物的幸运能力。",
    summary_is_community: true,
    created_at: "2024-02-01T10:00:00Z",
    updated_at: "2024-03-12T09:15:00Z",
  },
  {
    id: 3,
    guid: "term-guid-003",
    word: "倔强",
    tag: "adjective",
    meaning: "固执而不肯屈服；刚强",
    other_meaning: "坚定，不服软",
    note: "含有褒义，形容人意志坚定，不轻易妥协",
    channal: "channel-002",
    studio: mockStudio,
    editor: mockEditor,
    role: "manager",
    exp: 78,
    language: "zh",
    community: false,
    created_at: "2024-01-20T16:45:00Z",
    updated_at: "2024-03-08T11:30:00Z",
  },
];

// ─── 日志条目类型 ───────────────────────────────────────────────────────────────

interface ILogEntry {
  time: string;
  scene: string;
  type: "onUpdate" | "onClose";
  data?: ITermDataResponse;
}

// ─── TermTest ─────────────────────────────────────────────────────────────────

const TermTest: React.FC = () => {
  // 场景二：受控模式
  const [controlledOpen, setControlledOpen] = useState(false);
  const [useControlled, setUseControlled] = useState(false);

  // 日志
  const [logs, setLogs] = useState<ILogEntry[]>([]);

  const pushLog = (
    scene: string,
    type: ILogEntry["type"],
    data?: ITermDataResponse
  ) => {
    setLogs((prev) => [
      { time: new Date().toLocaleTimeString("zh-CN"), scene, type, data },
      ...prev.slice(0, 19),
    ]);
  };

  // 场景三：词条列表（onUpdate 同步更新本地数据）
  const [terms, setTerms] = useState<ITermDataResponse[]>(MOCK_TERMS);

  const handleTermUpdate = (scene: string) => (value: ITermDataResponse) => {
    pushLog(scene, "onUpdate", value);
    setTerms((prev) => {
      const idx = prev.findIndex((t) => t.id === value.id);
      if (idx >= 0) {
        const next = [...prev];
        next[idx] = value;
        return next;
      }
      return [value, ...prev];
    });
  };

  // 场景三：表格列
  const columns = [
    {
      title: "词条",
      dataIndex: "word",
      key: "word",
      render: (word: string, record: ITermDataResponse) => (
        <Space>
          <Text strong>{word}</Text>
          <Tag>{record.tag}</Tag>
          {record.community && <Tag color="blue">社区</Tag>}
        </Space>
      ),
    },
    {
      title: "释义",
      dataIndex: "meaning",
      key: "meaning",
      render: (meaning: string, record: ITermDataResponse) => (
        <div>
          <div>{meaning}</div>
          {record.other_meaning && (
            <Text type="secondary" style={{ fontSize: 12 }}>
              {record.other_meaning}
            </Text>
          )}
        </div>
      ),
    },
    {
      title: "语言",
      dataIndex: "language",
      key: "language",
      render: (lang: string) => (
        <Tag color={lang === "en" ? "geekblue" : "green"}>
          {lang.toUpperCase()}
        </Tag>
      ),
    },
    {
      title: "操作",
      key: "action",
      render: (_: unknown, record: ITermDataResponse) => (
        // 多实例并存，验证互不干扰
        <TermModal
          trigger={
            <Button size="small" icon={<EditOutlined />} type="link">
              编辑
            </Button>
          }
          id={record.guid}
          word={record.word}
          studioName={mockStudio.studioName}
          channelId={record.channal}
          community={record.community}
          onUpdate={handleTermUpdate(`场景三·编辑「${record.word}」`)}
          onClose={() => pushLog(`场景三·「${record.word}」`, "onClose")}
        />
      ),
    },
  ];

  return (
    <div style={{ padding: 32, background: "#f5f6fa", minHeight: "100vh" }}>
      <title>TermTest — 组件集成测试页</title>
      {/* 页头 */}
      <div style={{ marginBottom: 32 }}>
        <Title level={2} style={{ marginBottom: 4 }}>
          <BookOutlined style={{ marginRight: 10, color: "#1677ff" }} />
          TermTest — 组件集成测试页
        </Title>
        <Text type="secondary">
          直接测试真实的 <code>TermModal</code> 与 <code>TermEdit</code>{" "}
          组件，所有回调输出记录在右侧日志区
        </Text>
      </div>

      <Row gutter={[24, 24]}>
        {/* ── 左侧：测试场景区 ── */}
        <Col span={24} xl={16}>
          <Row gutter={[16, 16]}>
            {/* ── 场景一：Trigger 非受控 ── */}
            <Col span={24}>
              <Card
                title={
                  <Space>
                    <Badge color="blue" />
                    场景一：Trigger 触发（非受控）
                  </Space>
                }
                extra={<Tag color="blue">uncontrolled</Tag>}
              >
                <Paragraph
                  type="secondary"
                  style={{ fontSize: 13, marginBottom: 16 }}
                >
                  通过 <code>trigger</code> prop 触发弹窗，内部管理 open 状态。
                  覆盖：新建（无 id）、编辑（有 id + word）、社区词条、无
                  studioName。
                </Paragraph>
                <Space wrap>
                  {/* 1-A：新建，不传 id */}
                  <TermModal
                    trigger={
                      <Button type="primary" icon={<PlusOutlined />}>
                        新建词条（无 id）
                      </Button>
                    }
                    studioName={mockStudio.studioName}
                    channelId="channel-001"
                    onUpdate={handleTermUpdate("场景一A·新建")}
                    onClose={() => pushLog("场景一A·新建", "onClose")}
                  />

                  {/* 1-B：编辑，传入 id + word */}
                  <TermModal
                    trigger={
                      <Button icon={<EditOutlined />}>
                        编辑词条（有 id + word）
                      </Button>
                    }
                    id={MOCK_TERMS[0].guid}
                    word={MOCK_TERMS[0].word}
                    studioName={mockStudio.studioName}
                    channelId={MOCK_TERMS[0].channal}
                    onUpdate={handleTermUpdate("场景一B·编辑")}
                    onClose={() => pushLog("场景一B·编辑", "onClose")}
                  />

                  {/* 1-C：社区词条 */}
                  <TermModal
                    trigger={
                      <Button type="dashed" icon={<GlobalOutlined />}>
                        社区词条（community）
                      </Button>
                    }
                    word="ubiquitous"
                    studioName={mockStudio.studioName}
                    community={true}
                    onUpdate={handleTermUpdate("场景一C·社区")}
                    onClose={() => pushLog("场景一C·社区", "onClose")}
                  />

                  {/* 1-D：不传 studioName，验证 Studio 链接不渲染 */}
                  <TermModal
                    trigger={<Button>无 studioName</Button>}
                    channelId="channel-001"
                    onUpdate={handleTermUpdate("场景一D·无studioName")}
                    onClose={() => pushLog("场景一D·无studioName", "onClose")}
                  />
                </Space>
              </Card>
            </Col>

            {/* ── 场景二：受控模式 ── */}
            <Col span={24}>
              <Card
                title={
                  <Space>
                    <Badge color="purple" />
                    场景二：受控模式（open prop）
                  </Space>
                }
                extra={<Tag color="purple">controlled</Tag>}
              >
                <Paragraph
                  type="secondary"
                  style={{ fontSize: 13, marginBottom: 16 }}
                >
                  外部通过 <code>open</code> prop 控制弹窗，<code>onClose</code>{" "}
                  负责收起。先开启开关，再点「外部打开」按钮。
                </Paragraph>
                <Space align="center" style={{ marginBottom: 12 }}>
                  <Switch
                    checked={useControlled}
                    onChange={(val) => {
                      setUseControlled(val);
                      if (!val) setControlledOpen(false);
                    }}
                    checkedChildren="受控已开"
                    unCheckedChildren="受控已关"
                  />
                  <Button
                    type="primary"
                    ghost
                    disabled={!useControlled}
                    onClick={() => setControlledOpen(true)}
                  >
                    外部打开 Modal
                  </Button>
                  <Text type="secondary" style={{ fontSize: 12 }}>
                    open 值：
                    <code>
                      {useControlled ? String(controlledOpen) : "— (undefined)"}
                    </code>
                  </Text>
                </Space>

                {/* 受控模式：不传 trigger，open 由外部控制 */}
                <TermModal
                  open={useControlled ? controlledOpen : undefined}
                  studioName={mockStudio.studioName}
                  channelId="channel-002"
                  word={MOCK_TERMS[1].word}
                  id={MOCK_TERMS[1].guid}
                  onUpdate={handleTermUpdate("场景二·受控编辑")}
                  onClose={() => {
                    setControlledOpen(false);
                    pushLog("场景二·受控", "onClose");
                  }}
                />
              </Card>
            </Col>

            {/* ── 场景三：列表行内触发，多实例 ── */}
            <Col span={24}>
              <Card
                title={
                  <Space>
                    <Badge color="green" />
                    场景三：列表行内编辑触发（多实例并存）
                  </Space>
                }
                extra={
                  <Badge
                    count={terms.length}
                    style={{ backgroundColor: "#52c41a" }}
                  />
                }
              >
                <Paragraph
                  type="secondary"
                  style={{ fontSize: 13, marginBottom: 16 }}
                >
                  每行独立的 <code>TermModal</code>，验证多实例互不干扰。
                  <code>onUpdate</code> 会同步更新本地词条列表。
                </Paragraph>
                <Table
                  dataSource={terms}
                  columns={columns}
                  rowKey="guid"
                  size="middle"
                  pagination={false}
                />
              </Card>
            </Col>

            {/* ── 场景四：TermEdit 独立使用 ── */}
            <Col span={24}>
              <Card
                title={
                  <Space>
                    <Badge color="orange" />
                    场景四：TermEdit 独立嵌入（无 Modal 包裹）
                  </Space>
                }
                extra={<Tag color="orange">TermEdit standalone</Tag>}
              >
                <Paragraph
                  type="secondary"
                  style={{ fontSize: 13, marginBottom: 16 }}
                >
                  直接渲染 <code>TermEdit</code>，不经过
                  Modal，验证其独立可用性。
                </Paragraph>
                <TermEdit
                  studioName={mockStudio.studioName}
                  channelId="channel-001"
                  parentChannelId="parent-channel-001"
                  parentStudioId={mockStudio.id}
                  onUpdate={handleTermUpdate("场景四·独立TermEdit")}
                />
              </Card>
            </Col>
          </Row>
        </Col>

        {/* ── 右侧：回调日志 ── */}
        <Col span={24} xl={8}>
          <Card
            title="回调日志"
            style={{ position: "sticky", top: 24 }}
            extra={
              <Button
                size="small"
                type="text"
                danger
                disabled={logs.length === 0}
                onClick={() => setLogs([])}
              >
                清空
              </Button>
            }
            styles={{
              body: {
                padding: "12px 16px",
                maxHeight: "80vh",
                overflowY: "auto",
              },
            }}
          >
            {logs.length === 0 ? (
              <div
                style={{
                  textAlign: "center",
                  padding: "48px 0",
                  color: "#bbb",
                }}
              >
                <BookOutlined
                  style={{ fontSize: 28, display: "block", marginBottom: 8 }}
                />
                <Text type="secondary">操作上方组件后此处显示回调</Text>
              </div>
            ) : (
              <Space direction="vertical" style={{ width: "100%" }} size={8}>
                {logs.map((log, i) => (
                  <div
                    key={i}
                    style={{
                      border: `1px solid ${
                        log.type === "onUpdate" ? "#b7eb8f" : "#ffe7ba"
                      }`,
                      borderRadius: 6,
                      padding: "8px 10px",
                      background:
                        log.type === "onUpdate" ? "#f6ffed" : "#fff7e6",
                      fontSize: 12,
                    }}
                  >
                    <div style={{ marginBottom: 4 }}>
                      <Tag
                        color={log.type === "onUpdate" ? "success" : "warning"}
                        style={{ fontSize: 11 }}
                      >
                        {log.type}
                      </Tag>
                      <Text type="secondary" style={{ fontSize: 11 }}>
                        {log.time}
                      </Text>
                      <Text
                        strong
                        style={{
                          display: "block",
                          marginTop: 2,
                          color: "#444",
                        }}
                      >
                        {log.scene}
                      </Text>
                    </div>
                    {log.data && (
                      <pre
                        style={{
                          margin: 0,
                          background: "#1e1e2e",
                          color: "#cdd6f4",
                          borderRadius: 4,
                          padding: "6px 8px",
                          fontSize: 11,
                          lineHeight: 1.5,
                          overflowX: "auto",
                          maxHeight: 160,
                          overflowY: "auto",
                        }}
                      >
                        {JSON.stringify(log.data, null, 2)}
                      </pre>
                    )}
                  </div>
                ))}
              </Space>
            )}
          </Card>
        </Col>
      </Row>
    </div>
  );
};

export default TermTest;
