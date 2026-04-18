import { Badge, Popover, Tag } from "antd";
import type { ITagMapData } from "../../api/tag";

import { useAppSelector } from "../../hooks";
import { tagList } from "../../reducers/discussion-count";

import TagSelectButton from "./TagSelectButton";
import { numToHex } from "../../utils";

interface IWidget {
  data?: ITagMapData[];
  max?: number;
  resId?: string;
  resType?: string;
  selectorTitle?: React.ReactNode;
}
const TagsAreaWidget = ({
  max = 5,
  resId,
  resType,
  selectorTitle,
}: IWidget) => {
  const tagMapList = useAppSelector(tagList);

  const tags = tagMapList?.filter((v) => v.anchor_id === resId);

  const currTags = tags?.map((item, id) => {
    return id < max ? (
      <Tag key={id} color={"#" + numToHex(item.color ?? 13684944)}>
        {item.name}
      </Tag>
    ) : undefined;
  });

  const extraTags = tags?.map((item, id) => {
    return id >= max ? (
      <Tag key={id} color={"#" + numToHex(item.color ?? 13684944)}>
        {item.name}
      </Tag>
    ) : undefined;
  });
  let extra = 0;
  if (tags && typeof max !== "undefined") {
    extra = tags.length - max;
  }
  if (extra < 0) {
    extra = 0;
  }

  return (
    <div style={{ width: "100%", lineHeight: "2em" }}>
      <TagSelectButton
        selectorTitle={selectorTitle}
        resId={resId}
        resType={resType}
        trigger={<span style={{ cursor: "pointer" }}>{currTags}</span>}
      />
      <Popover content={<div>{extraTags}</div>}>
        <Badge
          count={extra}
          style={{ backgroundColor: "#52c41a", cursor: "pointer" }}
        />
      </Popover>
    </div>
  );
};

export default TagsAreaWidget;
