import { Typography } from "antd";
import { Link } from "react-router-dom";

const { Title, Text } = Typography;

export interface IChapterInfo {
  title: string;
  subTitle?: string;
  summary?: string;
  cover?: string;
  book?: number;
  para?: number;
}
interface IWidgetPaliChapterHeading {
  data: IChapterInfo;
}
const ChapterHeadWidget = (prop: IWidgetPaliChapterHeading) => {
  return (
    <>
      <Title level={4}>
        <Link to={`/article/chapter/${prop.data.book}-${prop.data.para}`}>
          {prop.data.title}
        </Link>
      </Title>
      <div>
        <Text type="secondary">
          {prop.data.subTitle ? prop.data.subTitle : ""}
        </Text>
      </div>
    </>
  );
};

export default ChapterHeadWidget;
