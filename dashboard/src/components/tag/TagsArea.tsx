import { Tag } from "antd";
import { ITagMapData } from "../api/Tag";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../hooks";
import { tagList } from "../../reducers/discussion-count";
import { numToHex } from "./TagList";

interface IWidget {
  data?: ITagMapData[];
  max?: number;
  resId?: string;
  onTagClose?: Function;
  onTagClick?: Function;
}
const TagsAreaWidget = ({
  data = [],
  max = 5,
  resId,
  onTagClose,
  onTagClick,
}: IWidget) => {
  const [tags, setTags] = useState<ITagMapData[]>();

  const tagMapList = useAppSelector(tagList);

  useEffect(() => {
    if (tagMapList) {
      const currTags = tagMapList.filter((value) => value.anchor_id === resId);
      if (currTags) {
        setTags(currTags);
      }
    }
  }, [resId, tagMapList]);

  const currTags = tags?.map((item, id) => {
    return id < max ? (
      <Tag
        key={id}
        color={"#" + numToHex(item.color ?? 13684944)}
        closable
        onClose={() => {}}
      >
        {item.name}
      </Tag>
    ) : undefined;
  });
  return <div style={{ width: "100%", lineHeight: "2em" }}>{currTags}</div>;
};

export default TagsAreaWidget;
