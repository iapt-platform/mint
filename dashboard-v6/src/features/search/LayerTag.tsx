import { Tag } from "antd";
import { useIntl } from "react-intl";
import type { ChapterLayer } from "../../api/tipitaka-search";

const COLOR: Record<ChapterLayer, string> = {
  mula: "green",
  atthakatha: "blue",
  tika: "magenta",
  anna: "gold",
};

/** 根本 / 义注 / 复注 / 藏外 的彩色标签，全文与标题两个标签页共用 */
const LayerTag = ({ layer }: { layer?: string | null }) => {
  const intl = useIntl();
  if (!layer || !(layer in COLOR)) {
    return null;
  }
  return (
    <Tag
      color={COLOR[layer as ChapterLayer]}
      bordered={false}
      style={{ marginInlineEnd: 0 }}
    >
      {intl.formatMessage({ id: `search.layer.${layer}` })}
    </Tag>
  );
};

export default LayerTag;
