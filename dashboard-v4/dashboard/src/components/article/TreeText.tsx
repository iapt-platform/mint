import React, { useState, useCallback, useEffect } from "react";
import { Tree, Segmented, Spin } from "antd";
import type { TreeDataNode } from "antd";
import {
  BookOutlined,
  FileTextOutlined,
  FolderOutlined,
  FontSizeOutlined,
  TranslationOutlined,
} from "@ant-design/icons";
import { get } from "../../request";
import {
  IPaliListResponse,
  IPaliParagraphResponse,
  ISentenceListResponse,
} from "../api/Corpus";

// 定义节点类型
type NodeType =
  | "book"
  | "chapter"
  | "paragraph"
  | "sentence"
  | "text"
  | "resources"
  | "translations"
  | "similar"
  | "preview";

// 定义模式类型
type ParagraphMode = "preview" | "edit";

// 定义基础节点数据接口
interface BaseNodeData {
  id: string;
  title: string;
  type: NodeType;
  isLeaf?: boolean;
  preview?: string;
  content?: React.ReactNode;
  children?: BaseNodeData[];
}

// 定义树节点接口
interface TreeNode extends TreeDataNode {
  key: string;
  type: NodeType;
  isLeaf?: boolean;
  children?: TreeNode[];
}

// 定义API响应接口
interface ApiResponse extends BaseNodeData {}

// 定义组件状态接口
interface IWidget {
  type?: NodeType;
  rootId?: string;
  channelsId?: string[];
}
const TreeTextComponent = ({ type, rootId, channelsId }: IWidget) => {
  const [treeData, setTreeData] = useState<BaseNodeData[]>([]);
  const [loadingKeys, setLoadingKeys] = useState<string[]>([]);
  const [paragraphModes, setParagraphModes] = useState<
    Record<string, ParagraphMode>
  >({});

  useEffect(() => {
    if (type === "chapter") {
      const url = `/v2/palitext/${rootId}`;
      get<IPaliParagraphResponse>(url).then((json) => {
        if (json.ok) {
          setTreeData([
            {
              id: json.data.uid,
              title: json.data.text,
              type: "chapter",
            },
          ]);
        }
      });
    }
  }, [rootId, type]);

  // 模拟API调用
  const mockApiCall = useCallback(
    async (type: NodeType, key: string): Promise<ApiResponse[]> => {
      console.log("Calling API:", type, key);
      //await new Promise((resolve) => setTimeout(resolve, 1000)); // 模拟网络延迟

      // 模拟不同类型节点的响应数据
      if (type === "book") {
        return [
          { id: "chapter_1", title: "第一章", type: "chapter" },
          { id: "chapter_2", title: "第二章", type: "chapter" },
          { id: "chapter_3", title: "第三章", type: "chapter" },
        ];
      } else if (type === "chapter") {
        const url = `/v2/palitext?view=children&id=${key}`;
        const paragraphs = await get<IPaliListResponse>(url);
        return paragraphs.data.rows.map((item) => {
          if (item.level < 8) {
            return {
              id: item.uid,
              title: item.toc,
              type: "chapter",
            };
          } else {
            return {
              id: `${item.book}-${item.paragraph}`,
              title: item.paragraph.toString(),
              type: "paragraph",
              preview: item.text,
            };
          }
        });
      } else if (type === "paragraph") {
        const [book, paragraph] = key.split("-");
        const url = `/v2/sentence?view=paragraph&book=${book}&para=${paragraph}&channels=${channelsId?.join()}`;
        const res = await get<ISentenceListResponse>(url);
        return res.data.rows.map((item) => {
          return {
            id: item.id ?? "123",
            title: `${item.book}-${item.paragraph}-${item.word_start}-${item.word_end}`,
            type: "sentence",
            children: [
              {
                id: "text_node",
                title: item.content,
                type: "text",
                isLeaf: true,
              },
              {
                id: "resources",
                title: "资源",
                type: "resources",
                children: [
                  {
                    id: "translations",
                    title: "参考译文",
                    type: "translations",
                  },
                  {
                    id: "similar",
                    title: "相似句",
                    type: "similar",
                  },
                ],
              },
            ],
          };
        });
      } else if (type === "similar") {
        return [
          {
            id: "text_node",
            title: "句子文本：This is the original sentence text.",
            type: "text",
            isLeaf: true,
          },
          {
            id: "resources",
            title: "资源",
            type: "resources",
            children: [
              {
                id: "translations",
                title: "参考译文",
                type: "translations",
              },
              {
                id: "similar",
                title: "相似句",
                type: "similar",
              },
            ],
          },
        ];
      }
      return [];
    },
    [channelsId]
  );

  // 获取节点图标
  const getNodeIcon = (type: NodeType): React.ReactNode => {
    switch (type) {
      case "book":
        return <BookOutlined />;
      case "chapter":
        return <FolderOutlined />;
      case "paragraph":
        return <FileTextOutlined />;
      case "sentence":
        return <FontSizeOutlined />;
      case "translations":
        return <TranslationOutlined />;
      case "similar":
        return <FileTextOutlined />;
      default:
        return null;
    }
  };

  // 构建树节点
  const buildTreeNode = (
    node: BaseNodeData,
    parentKey: string = ""
  ): TreeNode => {
    const key = parentKey
      ? `${parentKey}_${node.id}`
      : `${node.type}_${node.id}`;
    const isLoading = loadingKeys.includes(key);

    let children: TreeNode[] = [];
    let hasChildren = false;

    if (node.type === "paragraph") {
      const mode = paragraphModes[key] || "preview";

      if (mode === "preview" && node.preview) {
        // 预览模式：只显示预览文字节点
        children = [
          {
            title: `预览：${node.preview}`,
            key: `${key}_preview`,
            type: "preview" as NodeType,
            isLeaf: true,
            icon: <FontSizeOutlined style={{ color: "#1890ff" }} />,
          },
        ];
      } else if (mode === "edit" && node.children) {
        // 编辑模式：显示子sentence节点
        children = node.children.map((child) => buildTreeNode(child, key));
      }

      hasChildren = Boolean(node.children && node.children.length > 0);
    } else if (node.children) {
      children = node.children.map((child) => buildTreeNode(child, key));
      hasChildren = true;
    } else if (
      !node.isLeaf &&
      ["book", "chapter", "paragraph", "sentence"].includes(node.type)
    ) {
      hasChildren = true;
    }

    const handleModeChange = (value: string | number): void => {
      setParagraphModes((prev) => ({
        ...prev,
        [key]: value as ParagraphMode,
      }));
    };

    const handleSegmentedClick = (e: React.MouseEvent): void => {
      e.stopPropagation();
    };

    const treeNode: TreeNode = {
      title: (
        <div style={{ display: "inline-block" }}>
          <div
            style={{
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              width: "100%",
            }}
          >
            {node.content ?? <span>{node.title}</span>}
            {node.type === "paragraph" && (
              <Segmented
                size="small"
                value={paragraphModes[key] || "preview"}
                onChange={handleModeChange}
                options={[
                  { label: "预览", value: "preview" },
                  { label: "编辑", value: "edit" },
                ]}
                style={{ marginLeft: 8 }}
                onClick={handleSegmentedClick}
              />
            )}
          </div>
        </div>
      ),
      key,
      type: node.type,
      icon: isLoading ? <Spin size="small" /> : getNodeIcon(node.type),
      isLeaf: node.isLeaf || (!hasChildren && node.type === "text"),
      children: children.length > 0 ? children : undefined,
    };

    return treeNode;
  };

  // 懒加载处理
  const onLoadData = async (node: TreeNode): Promise<void> => {
    const { key, type } = node;

    if (loadingKeys.includes(key)) return;

    setLoadingKeys((prev) => [...prev, key]);

    try {
      let apiUrl = "";
      const id = key.split("_").pop() || "";

      switch (type) {
        case "book":
          apiUrl = `/v2/book/${id}`;
          break;
        case "chapter":
          apiUrl = `/v2/chapter/${id}`;
          break;
        case "paragraph":
          apiUrl = `/v2/paragraph/${id}`;
          break;
        case "sentence":
          apiUrl = `/v2/sentence/${id}`;
          break;
        case "similar":
          apiUrl = `/v2/similar/${id}`;
          break;
        default:
          return;
      }

      const data = await mockApiCall(type, id);

      // 更新树数据
      const updateTreeData = (
        nodes: BaseNodeData[],
        parentKey: string = ""
      ): BaseNodeData[] => {
        return nodes.map((node) => {
          const currentKey = parentKey
            ? `${parentKey}_${node.id}`
            : `${node.type}_${node.id}`;
          if (currentKey === key) {
            return {
              ...node,
              children: data.map((child) => ({
                ...child,
                children: child.children || [],
              })),
            };
          } else if (node.children) {
            return {
              ...node,
              children: updateTreeData(node.children, currentKey),
            };
          }
          return node;
        });
      };

      setTreeData((prev) => updateTreeData(prev));
    } catch (error) {
      console.error("加载数据失败:", error);
    } finally {
      setLoadingKeys((prev) => prev.filter((k) => k !== key));
    }
  };

  return (
    <div
      style={{ padding: 20, backgroundColor: "#f5f5f5", minHeight: "100vh" }}
    >
      <div
        style={{
          backgroundColor: "white",
          padding: 20,
          borderRadius: 8,
          boxShadow: "0 2px 8px rgba(0,0,0,0.1)",
        }}
      >
        <h2 style={{ marginBottom: 20, color: "#1890ff" }}>
          树状文本展示组件 (TypeScript)
        </h2>
        <Tree
          showIcon
          showLine={true}
          loadData={onLoadData}
          treeData={treeData.map((node) => buildTreeNode(node))}
          style={{ fontSize: 14 }}
          blockNode
        />
      </div>

      {/* 使用说明 */}
      <div
        style={{
          marginTop: 20,
          backgroundColor: "white",
          padding: 20,
          borderRadius: 8,
          boxShadow: "0 2px 8px rgba(0,0,0,0.1)",
        }}
      >
        <h3>功能说明：</h3>
        <ul style={{ lineHeight: 1.8 }}>
          <li>📚 点击book节点懒加载chapter数据</li>
          <li>📄 点击chapter节点懒加载paragraph数据</li>
          <li>📝 paragraph节点右侧可切换预览/编辑模式</li>
          <li>👁️ 预览模式：只显示预览文字</li>
          <li>✏️ 编辑模式：显示子sentence节点</li>
          <li>📖 sentence节点包含句子文本和资源（参考译文、相似句）</li>
        </ul>

        <h4 style={{ marginTop: 20 }}>TypeScript 特性：</h4>
        <ul style={{ lineHeight: 1.8 }}>
          <li>🔒 完整的类型定义和类型安全</li>
          <li>📝 接口定义清晰，便于维护</li>
          <li>⚡ 更好的IDE支持和代码提示</li>
          <li>🛡️ 编译时错误检查</li>
        </ul>
      </div>
    </div>
  );
};

export default TreeTextComponent;
