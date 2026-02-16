import { Badge, Popover, Tag } from "antd";
import { ITagMapData } from "../api/Tag";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../hooks";
import { tagList } from "../../reducers/discussion-count";
import { numToHex } from "./TagList";
import TagSelectButton from "./TagSelectButton";

interface IWidget {
  data?: ITagMapData[];
  max?: number;
  resId?: string;
  resType?: string;
  selectorTitle?: React.ReactNode;
  onTagClose?: Function;
  onTagClick?: Function;
}
const TagsAreaWidget = ({
  data = [],
  max = 5,
  resId,
  resType,
  selectorTitle,
  onTagClose,
  onTagClick,
}: IWidget) => {
  const [tags, setTags] = useState<ITagMapData[]>();

  const tagMapList = useAppSelector(tagList);

  useEffect(() => {
    const currTags = tagMapList?.filter((value) => value.anchor_id === resId);
    setTags(currTags);
  }, [resId, tagMapList]);

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
