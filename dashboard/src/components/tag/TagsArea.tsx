import { Tag } from "antd";
import { ITagData } from "../api/Tag";

interface IWidget {
  data?: ITagData[];
  max?: number;
  onTagClose?: Function;
  onTagClick?: Function;
}
const TagsAreaWidget = ({ data, max = 5, onTagClose, onTagClick }: IWidget) => {
  const tags = data?.map((item, id) => {
    return id < max ? (
      <Tag key={id} closable onClose={() => {}}>
        {item.name}
      </Tag>
    ) : undefined;
  });
  return <div style={{ width: "100%", lineHeight: "2em" }}>{tags}</div>;
};

export default TagsAreaWidget;
