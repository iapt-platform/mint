import { MenuOutlined } from "@ant-design/icons";

import PaliTextToc from "./PaliTextToc";
import ToolButton from "./ToolButton";

interface IWidget {
  type?: string;
  articleId?: string;
}
const Widget = ({ type, articleId }: IWidget) => {
  const id = articleId?.split("_");
  let tocWidget = <></>;
  if (id && id.length > 0) {
    const sentId = id[0].split("-");
    if (sentId.length > 1) {
      tocWidget = (
        <PaliTextToc book={parseInt(sentId[0])} para={parseInt(sentId[1])} />
      );
    }
  }

  return (
    <ToolButton title="目录" icon={<MenuOutlined />} content={tocWidget} />
  );
};

export default Widget;
