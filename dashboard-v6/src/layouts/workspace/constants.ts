/** 工作区顶部工具栏高度（px） */
export const WORKSPACE_HEADER_HEIGHT = 56;

/** 工作区内容区（Content）的内边距（px） */
export const WORKSPACE_CONTENT_PADDING = 12;

/**
 * 内容区顶部到视口顶部的距离（px）：工具栏 + 内容区上内边距。
 * 吸顶元素（如右边栏）用它做偏移，吸顶位置与初始位置一致，滚动时不会跳动。
 */
export const WORKSPACE_STICKY_TOP =
  WORKSPACE_HEADER_HEIGHT + WORKSPACE_CONTENT_PADDING;
