import { Anchor } from "antd";
import { useEffect, useState, useRef } from "react";
import { convertToPlain } from "../../utils";

const { Link } = Anchor;

interface HeadingNode {
  key: string;
  label: string;
  level: number;
  children?: HeadingNode[];
}

interface Props {
  open?: boolean;
  containerSelector?: string; // 可指定扫描范围
}

/** 构建树结构 */
function buildTree(list: HeadingNode[]): HeadingNode[] {
  const root: HeadingNode = { key: "root", label: "", level: 0, children: [] };
  const stack = [root];

  for (const node of list) {
    while (stack.length && stack[stack.length - 1].level >= node.level) {
      stack.pop();
    }

    const parent = stack[stack.length - 1];
    parent.children ??= [];
    parent.children.push(node);

    stack.push(node);
  }

  return root.children ?? [];
}

/** 递归渲染 */
function renderLinks(nodes: HeadingNode[]): React.ReactNode {
  return nodes.map((node) => (
    <Link key={node.key} href={node.key} title={node.label}>
      {node.children && renderLinks(node.children)}
    </Link>
  ));
}

const AnchorNavWidget = ({ open = false, containerSelector }: Props) => {
  const [tree, setTree] = useState<HeadingNode[]>([]);
  const containerRef = useRef<HTMLElement | null>(null);

  /** 获取容器 */
  useEffect(() => {
    containerRef.current = containerSelector
      ? document.querySelector(containerSelector)
      : document.body;
  }, [containerSelector]);

  /** 扫描 heading */
  useEffect(() => {
    if (!open || !containerRef.current) return;

    const headings = Array.from(
      containerRef.current.querySelectorAll("h1,h2,h3,h4,h5,h6")
    );

    const list: HeadingNode[] = headings
      .map((el) => {
        if (!el.id) return null;

        return {
          key: `#${el.id}`,
          label: convertToPlain(el.innerHTML).slice(0, 30),
          level: Number(el.tagName[1]),
        };
      })
      .filter(Boolean) as HeadingNode[];

    setTree(buildTree(list));
  }, [open]);

  if (!open || tree.length === 0) return null;

  return (
    <div className="article_anchor paper_zh">
      <Anchor offsetTop={50}>{renderLinks(tree)}</Anchor>
    </div>
  );
};

export default AnchorNavWidget;
