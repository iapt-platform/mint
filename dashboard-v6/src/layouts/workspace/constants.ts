/** 工作区顶部工具栏高度（px） */
export const WORKSPACE_HEADER_HEIGHT = 56;

/** 工作区内容区（Content）的内边距（px） */
export const WORKSPACE_CONTENT_PADDING = 12;

/**
 * 内容区顶部到视口顶部的距离（px）：工具栏 + 内容区上内边距。
 * 吸顶元素（如右边栏）未吸顶时就在这个位置；吸顶时贴着工具栏（WORKSPACE_HEADER_HEIGHT），
 * 不留这 12px，否则正文会从工具栏与吸顶元素之间的空隙里滚过去。
 */
export const WORKSPACE_CONTENT_TOP =
  WORKSPACE_HEADER_HEIGHT + WORKSPACE_CONTENT_PADDING;
