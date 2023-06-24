import { Tag, Tooltip } from "antd";
import PaliText from "../template/Wbw/PaliText";

export interface ITagData {
  title: string;
  key: string;
  color?: string;
  count?: number;
  description?: string;
}

interface IWidget {
  data?: ITagData;
  color?: string;
  closable?: boolean;
  onTagClose?: Function;
  onTagClick?: Function;
}
const ChapterTagWidget = ({
  data,
  color,
  closable = false,
  onTagClick,
  onTagClose,
}: IWidget) => {
  return (
    <Tooltip placement="top" title={data?.title}>
      <Tag
        color={
          data?.title === "sutta"
            ? "gold"
            : data?.title === "vinaya"
            ? "green"
            : data?.title === "abhidhamma"
            ? "blue"
            : data?.title === "mūla"
            ? "#c4b30c"
            : data?.title === "aṭṭhakathā"
            ? "#79bb5c"
            : data?.title === "ṭīkā"
            ? "#2db7f5"
            : color
        }
        closable={closable}
        onClose={() => {
          if (typeof onTagClose !== "undefined") {
            onTagClose(data?.key);
          }
        }}
        style={{ cursor: "pointer", borderRadius: 6 }}
        onClick={() => {
          if (typeof onTagClick !== "undefined") {
            onTagClick(data?.key);
          }
        }}
      >
        <PaliText text={data?.title} />
        {data?.count ? `(${data.count})` : undefined}
      </Tag>
    </Tooltip>
  );
};

export default ChapterTagWidget;
