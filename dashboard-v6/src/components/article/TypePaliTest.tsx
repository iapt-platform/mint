// TypePaliTestPage.tsx
// 用于测试 TypePali 组件的调试页面
// 颜色完全跟随 antd 主题（通过 theme.useToken()），支持亮/暗主题切换

import { useState } from "react";
import {
  Card,
  Form,
  Input,
  Select,
  Button,
  Space,
  Divider,
  Tag,
  Typography,
  theme,
} from "antd";
import { PlayCircleOutlined, ReloadOutlined } from "@ant-design/icons";
import TypePali from "./TypePali"; // 根据实际路径调整
import type { ArticleMode, ArticleType } from "../../api/Article";

const { Title, Text } = Typography;
const { useToken } = theme;

interface TestParams {
  type: ArticleType;
  id: string;
  mode: ArticleMode;
  channelId: string;
  book: string;
  para: string;
  focus: string;
}

const DEFAULT_PARAMS: TestParams = {
  type: "chapter",
  id: "",
  mode: "read",
  channelId: "",
  book: "",
  para: "",
  focus: "",
};

const TypePaliTestPage = () => {
  const { token } = useToken();
  const [form] = Form.useForm<TestParams>();
  const [activeParams, setActiveParams] = useState<TestParams | null>(null);
  const [runCount, setRunCount] = useState(0);

  const handleRun = () => {
    const values = form.getFieldsValue();
    setActiveParams({ ...values });
    setRunCount((c) => c + 1);
  };

  const handleReset = () => {
    form.resetFields();
    setActiveParams(null);
    setRunCount(0);
  };

  return (
    <div
      style={{
        minHeight: "100vh",
        background: token.colorBgLayout,
        color: token.colorText,
        padding: token.paddingLG,
      }}
    >
      {/* Header */}
      <div style={{ marginBottom: token.marginXL }}>
        <Text type="secondary" style={{ fontSize: token.fontSizeSM }}>
          Dev Harness
        </Text>
        <Title level={3} style={{ margin: 0 }}>
          TypePali Widget Testbed
        </Title>
      </div>

      <div
        style={{
          display: "grid",
          gridTemplateColumns: "320px 1fr",
          gap: token.marginLG,
          alignItems: "start",
        }}
      >
        {/* Control Panel */}
        <Card
          size="small"
          title={
            <Text type="secondary" style={{ fontSize: token.fontSizeSM }}>
              Parameters
            </Text>
          }
          style={{ position: "sticky", top: token.marginLG }}
        >
          <Form
            form={form}
            layout="vertical"
            initialValues={DEFAULT_PARAMS}
            size="small"
          >
            <Form.Item
              label="type"
              name="type"
              style={{ marginBottom: token.marginSM }}
            >
              <Select
                options={[
                  { value: "chapter", label: "chapter" },
                  { value: "para", label: "para" },
                ]}
              />
            </Form.Item>

            <Form.Item
              label="id"
              name="id"
              style={{ marginBottom: token.marginSM }}
            >
              <Input placeholder="e.g. 12-34" />
            </Form.Item>

            <Form.Item
              label="mode"
              name="mode"
              style={{ marginBottom: token.marginSM }}
            >
              <Select
                options={[
                  { value: "read", label: "read" },
                  { value: "edit", label: "edit" },
                ]}
              />
            </Form.Item>

            <Form.Item
              label="channelId"
              name="channelId"
              style={{ marginBottom: token.marginSM }}
            >
              <Input placeholder="e.g. ch1_ch2" />
            </Form.Item>

            <Form.Item
              label="book"
              name="book"
              style={{ marginBottom: token.marginSM }}
            >
              <Input placeholder="e.g. 12" />
            </Form.Item>

            <Form.Item
              label="para"
              name="para"
              style={{ marginBottom: token.marginSM }}
            >
              <Input placeholder="e.g. 34" />
            </Form.Item>

            <Form.Item
              label="focus"
              name="focus"
              style={{ marginBottom: token.margin }}
            >
              <Input placeholder="e.g. 12-34 or 1-2-3-4" />
            </Form.Item>

            <Space
              style={{ width: "100%" }}
              direction="vertical"
              size={token.marginXS}
            >
              <Button
                type="primary"
                icon={<PlayCircleOutlined />}
                onClick={handleRun}
                block
              >
                Run Test
              </Button>
              <Button icon={<ReloadOutlined />} onClick={handleReset} block>
                Reset
              </Button>
            </Space>
          </Form>

          {/* Active params snapshot */}
          {activeParams && (
            <>
              <Divider style={{ margin: `${token.margin}px 0` }} />
              <Text
                type="secondary"
                style={{
                  fontSize: token.fontSizeSM,
                  display: "block",
                  marginBottom: token.marginXS,
                }}
              >
                Active — Run #{runCount}
              </Text>
              <div
                style={{
                  background: token.colorFillQuaternary,
                  borderRadius: token.borderRadiusSM,
                  padding: `${token.paddingXS}px ${token.paddingSM}px`,
                  fontSize: token.fontSizeSM,
                  lineHeight: 2,
                  border: `1px solid ${token.colorBorderSecondary}`,
                }}
              >
                {Object.entries(activeParams).map(([k, v]) =>
                  v ? (
                    <div
                      key={k}
                      style={{ display: "flex", alignItems: "center", gap: 6 }}
                    >
                      <Text
                        type="secondary"
                        style={{ fontSize: token.fontSizeSM, minWidth: 72 }}
                      >
                        {k}:
                      </Text>
                      <Tag color="processing" style={{ margin: 0 }}>
                        {v}
                      </Tag>
                    </div>
                  ) : null
                )}
              </div>
            </>
          )}
        </Card>

        {/* Render Area */}
        <Card
          size="small"
          title={
            <Text type="secondary" style={{ fontSize: token.fontSizeSM }}>
              Render Output
            </Text>
          }
          style={{ minHeight: 400 }}
        >
          {!activeParams ? (
            <div
              style={{
                display: "flex",
                flexDirection: "column",
                alignItems: "center",
                justifyContent: "center",
                minHeight: 300,
                gap: token.marginSM,
              }}
            >
              <div
                style={{
                  fontSize: 36,
                  lineHeight: 1,
                  color: token.colorTextDisabled,
                }}
              >
                ◎
              </div>
              <Text type="secondary">Set params and click Run Test</Text>
            </div>
          ) : (
            <div key={runCount}>
              <TypePali
                type={activeParams.type || undefined}
                id={activeParams.id || undefined}
                mode={activeParams.mode || "read"}
                channelId={activeParams.channelId || undefined}
                book={activeParams.book || undefined}
                para={activeParams.para || undefined}
                focus={activeParams.focus || undefined}
                onArticleChange={(type, id, target, param) => {
                  console.log("[onArticleChange]", { type, id, target, param });
                }}
                onLoad={(data) => {
                  console.log("[onLoad]", data);
                }}
                onTitle={(title) => {
                  console.log("[onTitle]", title);
                }}
              />
            </div>
          )}
        </Card>
      </div>
    </div>
  );
};

export default TypePaliTestPage;
