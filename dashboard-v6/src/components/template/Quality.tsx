import { Tag } from "antd";

/**
 * 质量标签取值 -> antd Tag 颜色映射
 * featured 精选   -> gold    金色
 * standard 标准   -> green   绿色
 * draft    草稿   -> default 默认灰色
 * pending  待定   -> orange  橙色
 */
const QUALITY_COLORS: Record<string, string> = {
  featured: "gold",
  standard: "green",
  draft: "default",
  pending: "orange",
};

interface IQualityCtl {
  value?: string;
}

/**
 * 渲染单个质量标签
 * 非法取值回退为默认的 pending，保证样式始终可预期
 */
const QualityCtl = ({ value = "pending" }: IQualityCtl) => {
  const color = QUALITY_COLORS[value] ?? QUALITY_COLORS.pending;
  return <Tag color={color}>{value}</Tag>;
};

interface IWidget {
  props: string;
}

const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IQualityCtl;
  return <QualityCtl {...prop} />;
};

export default Widget;
