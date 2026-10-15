import { Tag } from "antd";

const COLORS: Record<string, string> = {
  error: "red",
  urgent: "red",
  warning: "orange",
  suggestion: "blue",
  normal: "default",
};

interface IWidget {
  severity?: string | null;
}

export const SeverityTag = ({ severity }: IWidget) =>
  severity ? <Tag color={COLORS[severity] ?? "default"}>{severity}</Tag> : null;
