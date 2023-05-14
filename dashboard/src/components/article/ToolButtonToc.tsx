import { MenuOutlined } from "@ant-design/icons";
import { Key } from "antd/lib/table/interface";
import { ArticleType } from "./Article";

import PaliTextToc from "./PaliTextToc";
import ToolButton from "./ToolButton";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  onSelect?: Function;
}
const ToolButtonTocWidget = ({ type, articleId, onSelect }: IWidget) => {
  let tocWidget = <></>;

  switch (type) {
    case "chapter":
      const id = articleId?.split("_");
      if (id && id.length > 0) {
        const sentId = id[0].split("-");
        if (sentId.length > 1) {
          tocWidget = (
            <PaliTextToc
              book={parseInt(sentId[0])}
              para={parseInt(sentId[1])}
              onSelect={(selectedKeys: Key[]) => {
                if (
                  typeof onSelect !== "undefined" &&
                  selectedKeys.length > 0
                ) {
                  onSelect(selectedKeys[0]);
                }
              }}
            />
          );
        }
      }
      break;

    default:
      break;
  }

  return (
    <ToolButton title="目录" icon={<MenuOutlined />} content={tocWidget} />
  );
};

export default ToolButtonTocWidget;
