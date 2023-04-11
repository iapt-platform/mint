import ChapterTag, { ITagData } from "../corpus/ChapterTag";

interface IWidget {
  data: ITagData[];
  onTagClick?: Function;
}
const Widget = ({ data, onTagClick }: IWidget) => {
  // TODO
  const tags = data.map((item, id) => {
    return (
      <ChapterTag
        color="green"
        key={id}
        data={item}
        onTagClick={(key: string) => {
          if (typeof onTagClick !== "undefined") {
            onTagClick(key);
          }
        }}
      />
    );
  });
  return <>{tags}</>;
};

export default Widget;
