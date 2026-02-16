import { Typography } from "antd";
import PaliTextToc from "./PaliTextToc";

const { Title } = Typography;

interface IWidget {
  articleId?: string;
  channelId?: string | null;
  onArticleChange?: Function;
}
const TypeSeriesWidget = ({
  channelId,
  articleId,
  onArticleChange,
}: IWidget) => {
  return (
    <div>
      <Title level={3}>
        {"丛书："}
        {articleId}
      </Title>
      <Title level={4}>{"书目列表"}</Title>
      <PaliTextToc
        series={articleId}
        onClick={(
          id: string,
          e: React.MouseEvent<HTMLSpanElement, MouseEvent>
        ) => {
          if (typeof onArticleChange !== "undefined") {
            if (e.ctrlKey || e.metaKey) {
              onArticleChange("chapter", id, "_blank");
            } else {
              onArticleChange("chapter", id, "_self");
            }
          }
        }}
      />
    </div>
  );
};

export default TypeSeriesWidget;
