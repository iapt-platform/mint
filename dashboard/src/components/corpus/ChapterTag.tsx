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
  onTagClick?: Function;
}
const Widget = ({ data, color, onTagClick }: IWidget) => {
  return (
    <Tooltip placement="top" title={data?.title}>
      <Tag
        color={color}
        style={{ cursor: "pointer" }}
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

export default Widget;
