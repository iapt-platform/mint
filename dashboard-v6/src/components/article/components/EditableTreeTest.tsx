import { useState } from "react";
import { Card, Typography, Divider, Tag, Button } from "antd";
import EditableTreeWidget, {
  type ListNodeData,
  type TreeNodeData,
} from "./EditableTree";

const { Title, Text, Paragraph } = Typography;

const mockInitValue: ListNodeData[] = [
  { key: "1", title: "第一章", title_text: "第一章", level: 1 },
  { key: "2", title: "1.1 节", title_text: "1.1 节", level: 2 },
  { key: "3", title: "1.2 节", title_text: "1.2 节", level: 2 },
  { key: "4", title: "第二章", title_text: "第二章", level: 1 },
  { key: "5", title: "2.1 节", title_text: "2.1 节", level: 2 },
  { key: "6", title: "2.2 节", title_text: "2.2 节", level: 2 },
  { key: "7", title: "2.2.1 小节", title_text: "2.2.1 小节", level: 3 },
];

// ─── 非受控模式 Demo ───────────────────────────────────────────────
const UncontrolledDemo = () => {
  const [log, setLog] = useState<string[]>([]);

  const addLog = (msg: string) =>
    setLog((prev) => [
      `[${new Date().toLocaleTimeString()}] ${msg}`,
      ...prev.slice(0, 9),
    ]);

  return (
    <Card title="非受控模式（initValue）" style={{ marginBottom: 24 }}>
      <Paragraph type="secondary">
        树结构由组件内部管理，父组件只监听 onChange 事件。
      </Paragraph>
      <EditableTreeWidget
        initValue={mockInitValue}
        onChange={(list) => addLog(`onChange: ${list?.length ?? 0} 个节点`)}
        onSave={(list) =>
          addLog(`onSave: ${JSON.stringify(list?.map((i) => i.title_text))}`)
        }
        onAppend={async (node) => {
          const id = `new-${Date.now()}`;
          addLog(`onAppend: 在 "${node.title_text || "根"}" 下新增`);
          return {
            key: id,
            id,
            title: `新文章 ${id.slice(-4)}`,
            title_text: `新文章 ${id.slice(-4)}`,
            children: [],
            level: 1,
          };
        }}
        onTitleClick={(_e, node) =>
          addLog(`onTitleClick: "${node.title_text}"`)
        }
        onSelect={(keys) => addLog(`onSelect: ${JSON.stringify(keys)}`)}
      />
      <Divider />
      <Title level={5}>事件日志</Title>
      {log.length === 0 ? (
        <Text type="secondary">暂无事件</Text>
      ) : (
        log.map((item, i) => (
          <div key={i}>
            <Text code>{item}</Text>
          </div>
        ))
      )}
    </Card>
  );
};

// ─── 受控模式 Demo ─────────────────────────────────────────────────
const ControlledDemo = () => {
  const [value, setValue] = useState<ListNodeData[]>(mockInitValue);
  const [log, setLog] = useState<string[]>([]);

  const addLog = (msg: string) =>
    setLog((prev) => [
      `[${new Date().toLocaleTimeString()}] ${msg}`,
      ...prev.slice(0, 9),
    ]);

  const handleExternalAdd = () => {
    const id = `ext-${Date.now()}`;
    setValue((prev) => [
      ...prev,
      {
        key: id,
        title: `外部添加 ${id.slice(-4)}`,
        title_text: `外部添加 ${id.slice(-4)}`,
        level: 1,
      },
    ]);
    addLog("父组件外部添加了一个节点");
  };

  const handleReset = () => {
    setValue(mockInitValue);
    addLog("父组件重置了数据");
  };

  const handleClear = () => {
    setValue([]);
    addLog("父组件清空了数据");
  };

  return (
    <Card title="受控模式（value）" style={{ marginBottom: 24 }}>
      <Paragraph type="secondary">
        树结构由父组件通过 value 控制，onChange 时父组件更新 value。
      </Paragraph>
      <div
        style={{ marginBottom: 12, display: "flex", gap: 8, flexWrap: "wrap" }}
      >
        <Button onClick={handleExternalAdd}>外部添加节点</Button>
        <Button onClick={handleReset}>重置数据</Button>
        <Button danger onClick={handleClear}>
          清空数据
        </Button>
        <Tag color="blue">当前节点数：{value.length}</Tag>
      </div>
      <EditableTreeWidget
        value={value}
        onChange={(list) => {
          setValue(list ?? []);
          addLog(`onChange: ${list?.length ?? 0} 个节点`);
        }}
        onSave={(list) =>
          addLog(`onSave: ${JSON.stringify(list?.map((i) => i.title_text))}`)
        }
        onAppend={async (node) => {
          const id = `new-${Date.now()}`;
          addLog(`onAppend: 在 "${node.title_text || "根"}" 下新增`);
          return {
            key: id,
            id,
            title: `新文章 ${id.slice(-4)}`,
            title_text: `新文章 ${id.slice(-4)}`,
            children: [],
            level: 1,
          };
        }}
        onTitleClick={(_e, node) =>
          addLog(`onTitleClick: "${node.title_text}"`)
        }
        onSelect={(keys) => addLog(`onSelect: ${JSON.stringify(keys)}`)}
      />
      <Divider />
      <Title level={5}>事件日志</Title>
      {log.length === 0 ? (
        <Text type="secondary">暂无事件</Text>
      ) : (
        log.map((item, i) => (
          <div key={i}>
            <Text code>{item}</Text>
          </div>
        ))
      )}
    </Card>
  );
};

// ─── updatedNode / addOnArticle 注入 Demo ─────────────────────────
const InjectionDemo = () => {
  const [addOnArticle, setAddOnArticle] = useState<TreeNodeData | undefined>(
    undefined
  );
  const [updatedNode, setUpdatedNode] = useState<TreeNodeData | undefined>(
    undefined
  );
  const [log, setLog] = useState<string[]>([]);

  const addLog = (msg: string) =>
    setLog((prev) => [
      `[${new Date().toLocaleTimeString()}] ${msg}`,
      ...prev.slice(0, 9),
    ]);

  const handleInjectArticle = () => {
    const id = `inject-${Date.now()}`;
    const node: TreeNodeData = {
      key: id,
      id,
      title: `注入文章 ${id.slice(-4)}`,
      title_text: `注入文章 ${id.slice(-4)}`,
      children: [],
      level: 1,
    };
    setAddOnArticle(node);
    addLog(`注入 addOnArticle: "${node.title_text}"`);
  };

  const handleUpdateNode = () => {
    const node: TreeNodeData = {
      key: "1",
      id: "1",
      title: `第一章（已更新 ${Date.now().toString().slice(-4)}）`,
      title_text: `第一章（已更新）`,
      children: [],
      level: 1,
    };
    setUpdatedNode(node);
    addLog(`注入 updatedNode: id=1 标题已更新`);
  };

  return (
    <Card title="addOnArticle / updatedNode 注入 Demo">
      <Paragraph type="secondary">
        测试通过 props 向组件注入新节点或更新已有节点标题。
      </Paragraph>
      <div style={{ marginBottom: 12, display: "flex", gap: 8 }}>
        <Button onClick={handleInjectArticle}>注入 addOnArticle</Button>
        <Button onClick={handleUpdateNode}>更新节点 id=1 标题</Button>
      </div>
      <EditableTreeWidget
        initValue={mockInitValue}
        addOnArticle={addOnArticle}
        updatedNode={updatedNode}
        onChange={(list) => addLog(`onChange: ${list?.length ?? 0} 个节点`)}
        onSave={(list) => addLog(`onSave: ${list?.length ?? 0} 个节点`)}
        onAppend={async (parent) => {
          const id = `new-${Date.now()}`;
          return {
            key: id,
            id,
            title: `新文章 ${id.slice(-4)}`,
            title_text: `新文章 ${id.slice(-4)}`,
            children: [],
            level: parent.level + 1,
          };
        }}
        onTitleClick={(_e, node) =>
          addLog(`onTitleClick: "${node.title_text}"`)
        }
      />
      <Divider />
      <Title level={5}>事件日志</Title>
      {log.length === 0 ? (
        <Text type="secondary">暂无事件</Text>
      ) : (
        log.map((item, i) => (
          <div key={i}>
            <Text code>{item}</Text>
          </div>
        ))
      )}
    </Card>
  );
};

// ─── 主测试页面 ────────────────────────────────────────────────────
const EditableTreeTestPage = () => {
  const [activeDemo, setActiveDemo] = useState<
    "uncontrolled" | "controlled" | "injection"
  >("uncontrolled");

  const demos = {
    uncontrolled: <UncontrolledDemo />,
    controlled: <ControlledDemo />,
    injection: <InjectionDemo />,
  };

  return (
    <div style={{ padding: 24, maxWidth: 800, margin: "0 auto" }}>
      <Title level={3}>EditableTreeWidget 测试页</Title>
      <div style={{ marginBottom: 24, display: "flex", gap: 8 }}>
        <Button
          type={activeDemo === "uncontrolled" ? "primary" : "default"}
          onClick={() => setActiveDemo("uncontrolled")}
        >
          非受控模式
        </Button>
        <Button
          type={activeDemo === "controlled" ? "primary" : "default"}
          onClick={() => setActiveDemo("controlled")}
        >
          受控模式
        </Button>
        <Button
          type={activeDemo === "injection" ? "primary" : "default"}
          onClick={() => setActiveDemo("injection")}
        >
          Props 注入
        </Button>
      </div>
      {demos[activeDemo]}
    </div>
  );
};

export default EditableTreeTestPage;
