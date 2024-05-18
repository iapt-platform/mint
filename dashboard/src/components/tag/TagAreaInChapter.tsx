import ChapterTag, { ITagData } from "../corpus/ChapterTag";

interface IWidget {
  data: ITagData[];
  closable?: boolean;
  max?: number;
  onTagClose?: Function;
  onTagClick?: Function;
}
const TagAreaWidget = ({
  data,
  closable,
  max = 10000,
  onTagClose,
  onTagClick,
}: IWidget) => {
  const tags = data.map((item, id) => {
    return id < max ? (
      <ChapterTag
        key={id}
        data={item}
        closable={closable}
        onTagClose={(key: string) => {
          if (typeof onTagClose !== "undefined") {
            onTagClose(key);
          }
        }}
        onTagClick={(key: string) => {
          if (typeof onTagClick !== "undefined") {
            onTagClick(key);
          }
        }}
      />
    ) : undefined;
  });
  return <div style={{ width: "100%", lineHeight: "2em" }}>{tags}</div>;
};

export default TagAreaWidget;
