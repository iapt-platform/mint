/** 主导航「工具」的子项，值是菜单 key；点开的是弹窗（ToolsModals），不是路由 */
export const TOOL_KEYS = [
  "tool:dictionary",
  "tool:grammar-book",
  "tool:dict",
  "tool:tag",
  "tool:driver",
] as const;
export type ToolKey = (typeof TOOL_KEYS)[number];

export const isToolKey = (key: string): key is ToolKey =>
  (TOOL_KEYS as readonly string[]).includes(key);
