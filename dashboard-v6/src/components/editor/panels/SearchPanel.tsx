interface SearchPanelProps {
  articleId?: string;
  anthologyId?: string;
}

/**
 * 搜索面板（占位，按实际业务填充）
 */
export default function SearchPanel({
  articleId,
  anthologyId,
}: SearchPanelProps) {
  console.debug("panel render", articleId, anthologyId);
  return (
    <div style={{ padding: 16 }}>
      {/* TODO: 实现搜索业务逻辑 */}
      <div style={{ marginTop: 12 }} />
    </div>
  );
}
