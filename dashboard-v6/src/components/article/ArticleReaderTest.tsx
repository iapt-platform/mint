import { useState } from "react";
import {
  Card,
  Form,
  Input,
  Select,
  Switch,
  Button,
  Divider,
  Tag,
  Space,
  Typography,
  Row,
  Col,
  Badge,
  theme,
  ConfigProvider,
} from "antd";
import {
  PlayCircleOutlined,
  ReloadOutlined,
  CodeOutlined,
  EyeOutlined,
} from "@ant-design/icons";
import ArticleReader from "./ArticleReader";
import type { ArticleMode } from "../../api/Article";

const { darkAlgorithm } = theme;
const { Title, Text } = Typography;
const { useToken } = theme;

interface ITestParams {
  articleId: string;
  mode: ArticleMode;
  channelId: string;
  anthologyId: string;
  hideInteractive: boolean;
  hideTitle: boolean;
  isSubWindow: boolean;
  parentChannels: string;
}

const DEFAULT_PARAMS: ITestParams = {
  articleId: "",
  mode: "read",
  channelId: "",
  anthologyId: "",
  hideInteractive: false,
  hideTitle: false,
  isSubWindow: false,
  parentChannels: "",
};

const ARTICLE_MODE_OPTIONS: {
  label: string;
  value: ArticleMode;
  color: string;
}[] = [
  { label: "read", value: "read", color: "blue" },
  { label: "edit", value: "edit", color: "orange" },
  { label: "wbw", value: "wbw", color: "purple" },
  { label: "auto", value: "auto", color: "green" },
];

const BOOL_FIELDS: { name: keyof ITestParams; label: string }[] = [
  { name: "hideInteractive", label: "hideInteractive" },
  { name: "hideTitle", label: "hideTitle" },
  { name: "isSubWindow", label: "isSubWindow" },
];

const ArticleReaderTest = () => {
  const { token } = useToken();
  const [form] = Form.useForm<ITestParams>();
  const [activeParams, setActiveParams] = useState<ITestParams | null>(null);
  const [renderKey, setRenderKey] = useState(0);
  const [showParams, setShowParams] = useState(false);

  const handleRun = () => {
    form.validateFields().then((values) => {
      setActiveParams({ ...values });
      setRenderKey((k) => k + 1);
    });
  };

  const handleReset = () => {
    form.resetFields();
    setActiveParams(null);
  };

  return (
    <div style={{ padding: token.paddingLG }}>
      {/* Header */}
      <Space align="center" style={{ marginBottom: token.marginLG }}>
        <Badge status="processing" />
        <Title level={4} style={{ margin: 0 }}>
          {"<ArticleReader />"} Testbench
        </Title>
      </Space>

      <Row gutter={token.marginLG}>
        {/* Control Panel */}

        <Col xs={24} lg={8}>
          {/* 左侧栏 — 局部 dark 主题 */}
          <ConfigProvider theme={{ algorithm: darkAlgorithm }}>
            <Card
              title={
                <Space>
                  <CodeOutlined />
                  <span>Props</span>
                </Space>
              }
              size="small"
            >
              <Form
                form={form}
                layout="vertical"
                initialValues={DEFAULT_PARAMS}
              >
                <Form.Item
                  label="articleId"
                  name="articleId"
                  rules={[{ required: true, message: "articleId 必填" }]}
                >
                  <Input placeholder="e.g. article-001" />
                </Form.Item>

                <Form.Item label="mode" name="mode">
                  <Select
                    options={ARTICLE_MODE_OPTIONS.map((opt) => ({
                      value: opt.value,
                      label: <Tag color={opt.color}>{opt.label}</Tag>,
                    }))}
                  />
                </Form.Item>

                <Form.Item label="channelId" name="channelId">
                  <Input placeholder="e.g. channel_001" />
                </Form.Item>

                <Form.Item label="anthologyId" name="anthologyId">
                  <Input placeholder="e.g. anthology-001" />
                </Form.Item>

                <Form.Item
                  label={
                    <Space size={4}>
                      <span>parentChannels</span>
                      <Text
                        type="secondary"
                        style={{ fontSize: token.fontSizeSM }}
                      >
                        (逗号分隔)
                      </Text>
                    </Space>
                  }
                  name="parentChannels"
                >
                  <Input placeholder="e.g. ch1,ch2,ch3" />
                </Form.Item>

                <Divider />

                {BOOL_FIELDS.map(({ name, label }) => (
                  <Form.Item
                    key={name}
                    name={name}
                    valuePropName="checked"
                    style={{ marginBottom: token.marginSM }}
                  >
                    <Space
                      style={{ justifyContent: "space-between", width: "100%" }}
                    >
                      <Text>{label}</Text>
                      <Switch size="small" />
                    </Space>
                  </Form.Item>
                ))}

                <Divider />

                <Space orientation="vertical" style={{ width: "100%" }}>
                  <Button
                    type="primary"
                    icon={<PlayCircleOutlined />}
                    onClick={handleRun}
                    block
                  >
                    RUN
                  </Button>
                  <Button icon={<ReloadOutlined />} onClick={handleReset} block>
                    Reset
                  </Button>
                </Space>
              </Form>
            </Card>
          </ConfigProvider>
          {/* Active params display */}
          {activeParams && (
            <Card
              size="small"
              style={{ marginTop: token.marginSM }}
              title={
                <Space
                  style={{ justifyContent: "space-between", width: "100%" }}
                >
                  <Text type="secondary" style={{ fontSize: token.fontSizeSM }}>
                    ACTIVE PROPS
                  </Text>
                  <Button
                    type="link"
                    size="small"
                    icon={<EyeOutlined />}
                    onClick={() => setShowParams((v) => !v)}
                  >
                    {showParams ? "收起" : "展开"}
                  </Button>
                </Space>
              }
            >
              {showParams ? (
                <pre
                  style={{
                    margin: 0,
                    fontSize: token.fontSizeSM,
                    whiteSpace: "pre-wrap",
                    wordBreak: "break-all",
                  }}
                >
                  {JSON.stringify(activeParams, null, 2)}
                </pre>
              ) : (
                <Space wrap size={[4, 4]}>
                  {Object.entries(activeParams)
                    .filter(([, v]) => v !== "" && v !== false)
                    .map(([k, v]) => (
                      <Tag key={k}>
                        {k}={String(v)}
                      </Tag>
                    ))}
                </Space>
              )}
            </Card>
          )}
        </Col>

        {/* Preview Panel */}
        <Col xs={24} lg={16}>
          <Card
            title={
              <Space>
                <EyeOutlined />
                <span>Preview</span>
                {activeParams && (
                  <Tag
                    color={
                      ARTICLE_MODE_OPTIONS.find(
                        (o) => o.value === activeParams.mode
                      )?.color
                    }
                  >
                    {activeParams.mode}
                  </Tag>
                )}
              </Space>
            }
            size="small"
            style={{ minHeight: 480 }}
          >
            {!activeParams ? (
              <div
                style={{
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  minHeight: 360,
                  gap: token.marginSM,
                }}
              >
                <PlayCircleOutlined
                  style={{ fontSize: 32, color: token.colorTextDisabled }}
                />
                <Text type="secondary">填写参数后点击 RUN 渲染组件</Text>
              </div>
            ) : (
              <ArticleReader
                key={renderKey}
                articleId={activeParams.articleId || undefined}
                mode={activeParams.mode}
                channelId={activeParams.channelId || null}
                anthologyId={activeParams.anthologyId || null}
                parentChannels={
                  activeParams.parentChannels
                    ? activeParams.parentChannels
                        .split(",")
                        .map((s) => s.trim())
                        .filter(Boolean)
                    : []
                }
                hideInteractive={activeParams.hideInteractive}
                hideTitle={activeParams.hideTitle}
                isSubWindow={activeParams.isSubWindow}
                onArticleChange={(type, id, target) => {
                  console.log("[onArticleChange]", { type, id, target });
                }}
                onAnthologySelect={(id) => {
                  console.log("[onAnthologySelect]", { id });
                }}
                onEdit={() => {
                  console.log("[onEdit]");
                }}
              />
            )}
          </Card>

          <Text
            type="secondary"
            style={{
              display: "block",
              marginTop: token.marginXS,
              fontSize: token.fontSizeSM,
            }}
          >
            💡 onArticleChange / onAnthologySelect / onEdit
            回调输出至浏览器控制台
          </Text>
        </Col>
      </Row>
    </div>
  );
};

export default ArticleReaderTest;
