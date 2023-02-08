import { Tag } from "antd";
import { ITagData } from "../corpus/ChapterTagList";

interface IWidget {
  data: ITagData[];
  onTagClick?: Function;
}
const Widget = ({ data, onTagClick }: IWidget) => {
  // TODO
  const tags = data.map((item, id) => {
    return (
      <Tag
        color="green"
        key={id}
        style={{ cursor: "pointer" }}
        onClick={() => {
          if (typeof onTagClick !== "undefined") {
            onTagClick(item.key);
          }
        }}
      >
        {item.title}
      </Tag>
    );
  });
  return <>{tags}</>;
};

export default Widget;
