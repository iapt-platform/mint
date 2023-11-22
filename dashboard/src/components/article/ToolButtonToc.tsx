import { MenuOutlined } from "@ant-design/icons";
import { Key } from "antd/lib/table/interface";
import AnthologyTocTree from "../anthology/AnthologyTocTree";
import { ArticleType } from "./Article";

import PaliTextToc from "./PaliTextToc";
import ToolButton from "./ToolButton";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  anthologyId?: string | null;
  channels?: string[];
  onSelect?: Function;
}
const ToolButtonTocWidget = ({
  type,
  articleId,
  anthologyId,
  channels,
  onSelect,
}: IWidget) => {
  //TODO 都放return里面
  let tocWidget = <></>;
  if (type === "chapter" || type === "para") {
    if (articleId) {
      const sentId = articleId.split("-");
      if (sentId.length > 1) {
        tocWidget = (
          <PaliTextToc
            book={parseInt(sentId[0])}
            para={parseInt(sentId[1])}
            onSelect={(selectedKeys: Key[]) => {
              if (typeof onSelect !== "undefined" && selectedKeys.length > 0) {
                onSelect(selectedKeys[0]);
              }
            }}
          />
        );
      }
    }
  } else if (type === "article") {
    if (anthologyId) {
      tocWidget = (
        <AnthologyTocTree
          anthologyId={anthologyId}
          channels={channels}
          onArticleSelect={(anthologyId: string, keys: string[]) => {
            if (typeof onSelect !== "undefined" && keys.length > 0) {
              onSelect(keys[0]);
            }
          }}
        />
      );
    }
  }

  return (
    <ToolButton title="目录" icon={<MenuOutlined />} content={tocWidget} />
  );
};

export default ToolButtonTocWidget;
