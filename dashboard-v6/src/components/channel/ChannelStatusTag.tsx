import { Tag } from "antd";
import { useIntl } from "react-intl";

/** channel 的 status → 文案 key 与标签颜色 */
const STATUS: Record<number, { label: string; color: string }> = {
  0: { label: "disable", color: "default" },
  5: { label: "blocked", color: "red" },
  10: { label: "private", color: "orange" },
  20: { label: "public_no_list", color: "blue" },
  30: { label: "public", color: "green" },
  40: { label: "public.edit", color: "cyan" },
};

/** channel 公开性标签，按 status 着色；未知的 status 不显示 */
const ChannelStatusTag = ({ status }: { status?: number | null }) => {
  const intl = useIntl();
  const item =
    status === undefined || status === null ? undefined : STATUS[status];
  if (!item) {
    return null;
  }

  return (
    <Tag color={item.color}>
      {intl.formatMessage({ id: `forms.fields.publicity.${item.label}.label` })}
    </Tag>
  );
};

export default ChannelStatusTag;
