import { useState, useMemo } from "react";
import { Layout, Tree, Typography, Empty, theme } from "antd";
import { Outlet, useNavigate, useLocation } from "react-router";
import { testRoutes, type TestRouteObject } from "../../routes/testRoutes";
import type { TreeDataNode } from "antd";

const { Sider, Content } = Layout;
const { Title, Text } = Typography;

// ─── 递归构建 antd TreeDataNode ───────────────────────────────────────────────

function buildTreeData(
  routes: TestRouteObject[],
  parentPath = "/test"
): TreeDataNode[] {
  return routes.map((route) => {
    const fullPath = `${parentPath}/${route.path}`;
    return {
      key: fullPath,
      title: route.label,
      children: route.children?.length
        ? buildTreeData(route.children, fullPath)
        : undefined,
      // 叶子节点（有 Component、无 children）才可点击跳转
      isLeaf: !route.children?.length,
    };
  });
}

// ─── 收集所有父节点 key，用于默认展开 ─────────────────────────────────────────

function collectParentKeys(
  routes: TestRouteObject[],
  parentPath = "/test"
): string[] {
  const keys: string[] = [];
  routes.forEach((route) => {
    const fullPath = `${parentPath}/${route.path}`;
    if (route.children?.length) {
      keys.push(fullPath);
      keys.push(...collectParentKeys(route.children, fullPath));
    }
  });
  return keys;
}

// ─── 根据当前路径找到所有祖先 key，用于展开当前选中节点的父级 ─────────────────

function getAncestorKeys(pathname: string): string[] {
  const parts = pathname.split("/").filter(Boolean); // ["test", "button", "basic"]
  const keys: string[] = [];
  for (let i = 1; i < parts.length; i++) {
    keys.push("/" + parts.slice(0, i).join("/"));
  }
  return keys;
}

// ─── TestLayout ───────────────────────────────────────────────────────────────

export default function TestLayout() {
  const navigate = useNavigate();
  const location = useLocation();
  const { token } = theme.useToken();

  const treeData = useMemo(() => buildTreeData(testRoutes), []);
  const allParentKeys = useMemo(() => collectParentKeys(testRoutes), []);

  const [expandedKeys, setExpandedKeys] = useState<string[]>(() => [
    ...allParentKeys,
    ...getAncestorKeys(location.pathname),
  ]);

  // 当路由变化时，确保当前节点的祖先都展开（用 useMemo 避免在 effect 中 setState）
  const mergedExpandedKeys = useMemo(() => {
    const ancestors = getAncestorKeys(location.pathname);
    const merged = new Set([...expandedKeys, ...ancestors]);
    return Array.from(merged);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [location.pathname]);

  const isIndexRoute =
    location.pathname === "/test" || location.pathname === "/test/";

  const handleSelect = (selectedKeys: React.Key[]) => {
    const key = selectedKeys[0] as string;
    if (key) navigate(key);
  };

  return (
    <Layout style={{ minHeight: "100vh", background: token.colorBgLayout }}>
      {/* ── 左侧栏 ── */}
      <Sider
        width={240}
        style={{
          background: token.colorBgContainer,
          borderRight: `1px solid ${token.colorBorderSecondary}`,
          overflow: "auto",
          position: "sticky",
          top: 0,
          height: "100vh",
        }}
      >
        {/* 标题 */}
        <div
          style={{
            padding: "16px 20px 12px",
            borderBottom: `1px solid ${token.colorBorderSecondary}`,
            marginBottom: 8,
          }}
        >
          <Text
            type="secondary"
            style={{
              fontSize: 11,
              letterSpacing: "0.08em",
              textTransform: "uppercase",
            }}
          >
            组件测试
          </Text>
          <Title level={5} style={{ margin: "4px 0 0", lineHeight: 1.4 }}>
            Test Playground
          </Title>
        </div>

        {/* Tree */}
        {treeData.length > 0 ? (
          <Tree
            treeData={treeData}
            selectedKeys={[location.pathname]}
            expandedKeys={mergedExpandedKeys}
            onExpand={(keys) => setExpandedKeys(keys as string[])}
            onSelect={handleSelect}
            blockNode
            style={{
              padding: "4px 8px",
              background: "transparent",
            }}
          />
        ) : (
          <Empty
            description="暂无测试组件"
            image={Empty.PRESENTED_IMAGE_SIMPLE}
            style={{ marginTop: 40 }}
          />
        )}
      </Sider>

      {/* ── 右侧内容 ── */}
      <Content
        style={{
          padding: 32,
          overflow: "auto",
          minHeight: "100vh",
        }}
      >
        {isIndexRoute ? (
          // 首页提示
          <div
            style={{
              display: "flex",
              flexDirection: "column",
              alignItems: "center",
              justifyContent: "center",
              height: "60vh",
              gap: 12,
            }}
          >
            <div style={{ fontSize: 48 }}>🧪</div>
            <Title level={3} style={{ margin: 0 }}>
              组件测试平台
            </Title>
            <Text type="secondary">请从左侧目录选择一个组件开始测试</Text>
          </div>
        ) : (
          <Outlet />
        )}
      </Content>
    </Layout>
  );
}
