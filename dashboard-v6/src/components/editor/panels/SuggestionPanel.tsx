import SuggestionBox from "../../sentence/SuggestionBox";

interface SearchPanelProps {
  articleId?: string;
  anthologyId?: string;
}

/**
 * 搜索面板（占位，按实际业务填充）
 */
export default function SuggestionPanel({
  articleId,
  anthologyId,
}: SearchPanelProps) {
  console.debug("panel render", articleId, anthologyId);
  return (
    <div style={{ padding: 4 }}>
      <SuggestionBox />
    </div>
  );
}
