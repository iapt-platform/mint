import GrammarBook from "../../term/GrammarBook";

interface SearchPanelProps {
  articleId?: string;
  anthologyId?: string;
}

/**
 * 搜索面板（占位，按实际业务填充）
 */
export default function GrammarBookPanel({
  articleId,
  anthologyId,
}: SearchPanelProps) {
  console.debug("panel render", articleId, anthologyId);
  return (
    <div style={{ padding: 16 }}>
      <GrammarBook />
    </div>
  );
}
