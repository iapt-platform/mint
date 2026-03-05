import {
  BugOutlined,
  FileOutlined,
  FolderOutlined,
  SearchOutlined,
  CommentOutlined,
} from "@ant-design/icons";
import { Input, Segmented, Tree, Typography } from "antd";
import type { TreeDataNode } from "antd";
import { useState, type ReactNode } from "react";
import SplitLayout from "./SplitLayout";
import { useSplitLayout } from "./SplitLayoutContext";
import type { RightToolbarTab } from "./RightToolbar";

// ─────────────────────────────────────────────
// 模拟文件树数据
// ─────────────────────────────────────────────

const treeData: TreeDataNode[] = [
  {
    title: "group_vars",
    key: "group_vars",
    icon: <FolderOutlined />,
    children: [
      { title: "all.yml", key: "group_vars/all.yml", icon: <FileOutlined /> },
      {
        title: "production.yml",
        key: "group_vars/production.yml",
        icon: <FileOutlined />,
      },
    ],
  },
  {
    title: "roles",
    key: "roles",
    icon: <FolderOutlined />,
    children: [
      {
        title: "common",
        key: "roles/common",
        icon: <FolderOutlined />,
        children: [
          {
            title: "tasks",
            key: "roles/common/tasks",
            icon: <FolderOutlined />,
            children: [
              {
                title: "main.yml",
                key: "roles/common/tasks/main.yml",
                icon: <FileOutlined />,
              },
            ],
          },
        ],
      },
    ],
  },
  {
    title: "scripts",
    key: "scripts",
    icon: <FolderOutlined />,
    children: [
      { title: "deploy.sh", key: "scripts/deploy.sh", icon: <FileOutlined /> },
      {
        title: "rollback.sh",
        key: "scripts/rollback.sh",
        icon: <FileOutlined />,
      },
    ],
  },
  { title: ".gitignore", key: ".gitignore", icon: <FileOutlined /> },
  { title: "ansible.cfg", key: "ansible.cfg", icon: <FileOutlined /> },
];

const fileContent = `# group_vars/all.yml
ansible_user: deploy
ansible_ssh_private_key_file: ~/.ssh/id_rsa

app_name: mint
app_env: production
app_port: 8080

docker_registry: registry.example.com
docker_image: "{{ app_name }}:{{ app_version }}"

workers:
  ai_translate:
    replicas: 3
    image: mint-translate-worker
    env:
      MODEL: gpt-4o
      CONCURRENCY: 4`;

// ─────────────────────────────────────────────
// 右边栏 tabs 配置
// ─────────────────────────────────────────────

const rightTabs: RightToolbarTab[] = [
  { key: "chat", icon: <CommentOutlined />, label: "对话" },
  { key: "search", icon: <SearchOutlined />, label: "搜索" },
  { key: "debug", icon: <BugOutlined />, label: "调试" },
];

// ─────────────────────────────────────────────
// 右边栏面板内容
// ─────────────────────────────────────────────

const rightPanels: Record<string, ReactNode> = {
  chat: (
    <div style={{ padding: 16 }}>
      <Typography.Text type="secondary" style={{ fontSize: 12 }}>
        模拟对话面板
      </Typography.Text>
      <div
        style={{
          marginTop: 12,
          display: "flex",
          flexDirection: "column",
          gap: 8,
        }}
      >
        {[
          "部署流程是什么？",
          "如何回滚到上个版本？",
          "worker 数量如何调整？",
        ].map((q) => (
          <div
            key={q}
            style={{
              padding: "8px 12px",
              background: "var(--ant-color-fill-quaternary, #f5f5f5)",
              borderRadius: 6,
              fontSize: 13,
              cursor: "pointer",
            }}
          >
            {q}
          </div>
        ))}
      </div>
    </div>
  ),
  search: (
    <div style={{ padding: 16 }}>
      <Input.Search placeholder="搜索文件内容..." size="small" />
      <div style={{ marginTop: 12 }}>
        <Typography.Text type="secondary" style={{ fontSize: 12 }}>
          搜索结果将显示在此处
        </Typography.Text>
      </div>
    </div>
  ),
  debug: (
    <div style={{ padding: 16 }}>
      <Typography.Text type="secondary" style={{ fontSize: 12 }}>
        调试信息
      </Typography.Text>
      <pre
        style={{
          marginTop: 8,
          fontSize: 12,
          background: "var(--ant-color-fill-quaternary, #f5f5f5)",
          borderRadius: 6,
          padding: 12,
          overflow: "auto",
        }}
      >
        {JSON.stringify(
          { env: "production", workers: 3, status: "running" },
          null,
          2
        )}
      </pre>
    </div>
  ),
};

// ─────────────────────────────────────────────
// 共用样式
// ─────────────────────────────────────────────

const headerStyle: React.CSSProperties = {
  display: "flex",
  alignItems: "center",
  gap: 8,
  padding: "8px 16px",
  borderBottom: "1px solid var(--ant-color-split, #f0f0f0)",
  minHeight: 40,
  flexShrink: 0,
};

// ─────────────────────────────────────────────
// 模拟侧边栏
// ─────────────────────────────────────────────

function MockSidebar() {
  return (
    <Tree
      showIcon
      defaultExpandedKeys={["group_vars", "roles"]}
      treeData={treeData}
      style={{ padding: "8px 4px" }}
    />
  );
}

// ─────────────────────────────────────────────
// 方案 A：expandButton 由外部 render props 注入
// ─────────────────────────────────────────────

interface MockContentAProps {
  headerExtra?: ReactNode;
}

function MockContentA({ headerExtra }: MockContentAProps) {
  return (
    <div style={{ display: "flex", flexDirection: "column", height: "100%" }}>
      <div style={headerStyle}>
        {headerExtra}
        <Typography.Text type="secondary" style={{ fontSize: 13 }}>
          mint / deploy /
        </Typography.Text>
        <Typography.Text strong style={{ fontSize: 13 }}>
          group_vars
        </Typography.Text>
        <Typography.Text
          type="secondary"
          style={{ marginLeft: "auto", fontSize: 11 }}
        >
          方案 A · Render Props
        </Typography.Text>
      </div>
      <div style={{ flex: 1, padding: 24, overflow: "auto" }}>
        <Typography.Title level={5} style={{ marginTop: 0 }}>
          all.yml
        </Typography.Title>
        <Typography.Paragraph type="secondary" style={{ fontSize: 12 }}>
          Last commit: <strong>add multi ai-translate worker support</strong> ·
          11 months ago
        </Typography.Paragraph>
        <pre
          style={{
            background: "var(--ant-color-fill-quaternary,#f5f5f5)",
            borderRadius: 6,
            padding: 16,
            fontSize: 13,
            lineHeight: 1.7,
            overflow: "auto",
          }}
        >
          {fileContent}
        </pre>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────
// 方案 B：自己调用 useSplitLayout() 取 expandButton
// ─────────────────────────────────────────────

function MockContentB() {
  const { expandButton } = useSplitLayout();

  return (
    <div style={{ display: "flex", flexDirection: "column", height: "100%" }}>
      <div style={headerStyle}>
        {expandButton}
        <Typography.Text type="secondary" style={{ fontSize: 13 }}>
          mint / deploy /
        </Typography.Text>
        <Typography.Text strong style={{ fontSize: 13 }}>
          group_vars
        </Typography.Text>
        <Typography.Text
          type="secondary"
          style={{ marginLeft: "auto", fontSize: 11 }}
        >
          方案 B · useSplitLayout Hook
        </Typography.Text>
      </div>
      <div style={{ flex: 1, padding: 24, overflow: "auto" }}>
        <Typography.Title level={5} style={{ marginTop: 0 }}>
          all.yml
        </Typography.Title>
        <Typography.Paragraph type="secondary" style={{ fontSize: 12 }}>
          Last commit: <strong>add multi ai-translate worker support</strong> ·
          11 months ago
        </Typography.Paragraph>
        <pre
          style={{
            background: "var(--ant-color-fill-quaternary,#f5f5f5)",
            borderRadius: 6,
            padding: 16,
            fontSize: 13,
            lineHeight: 1.7,
            overflow: "auto",
          }}
        >
          {fileContent}
        </pre>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────
// 测试入口
// ─────────────────────────────────────────────

type Mode = "A" | "B";

export default function SplitLayoutTest() {
  const [mode, setMode] = useState<Mode>("A");

  return (
    <div style={{ height: "100vh", display: "flex", flexDirection: "column" }}>
      {/* 顶部切换条 */}
      <div
        style={{
          display: "flex",
          alignItems: "center",
          gap: 12,
          padding: "8px 16px",
          borderBottom: "1px solid var(--ant-color-split,#f0f0f0)",
          flexShrink: 0,
        }}
      >
        <Typography.Text type="secondary" style={{ fontSize: 13 }}>
          SplitLayout 测试
        </Typography.Text>
        <Segmented<Mode>
          size="small"
          value={mode}
          onChange={setMode}
          options={[
            { label: "方案 A · Render Props", value: "A" },
            { label: "方案 B · Hook", value: "B" },
          ]}
        />
      </div>

      {/* 方案 A */}
      {mode === "A" && (
        <SplitLayout
          key="mode-a"
          sidebarTitle="mint / deploy"
          sidebar={<MockSidebar />}
          rightTabs={rightTabs}
          rightPanels={rightPanels}
        >
          {({ expandButton }) => <MockContentA headerExtra={expandButton} />}
        </SplitLayout>
      )}

      {/* 方案 B */}
      {mode === "B" && (
        <SplitLayout
          key="mode-b"
          sidebarTitle="mint / deploy"
          sidebar={<MockSidebar />}
          rightTabs={rightTabs}
          rightPanels={rightPanels}
        >
          <MockContentB />
        </SplitLayout>
      )}
    </div>
  );
}
