import { MenuOutlined } from "@ant-design/icons";
import { Key } from "antd/lib/table/interface";
import AnthologyTocTree from "../anthology/AnthologyTocTree";
import { ArticleType } from "./Article";

import PaliTextToc from "./PaliTextToc";
import ToolButton from "./ToolButton";
import TextBookToc from "../anthology/TextBookToc";
import { useIntl } from "react-intl";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  anthologyId?: string | null;
  courseId?: string | null;
  channels?: string[];
  onSelect?: Function;
}
const ToolButtonTocWidget = ({
  type,
  articleId,
  anthologyId,
  courseId,
  channels,
  onSelect,
}: IWidget) => {
  const intl = useIntl();
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
          onClick={(anthology: string, article: string, target: string) => {
            if (typeof onSelect !== "undefined") {
              onSelect(article, target);
            }
          }}
        />
      );
    }
  } else if (type === "textbook") {
    tocWidget = (
      <TextBookToc
        courseId={courseId}
        channels={channels}
        onClick={(article: string, target: string) => {
          console.debug("TextBookToc onClick", article);
          if (typeof onSelect !== "undefined") {
            onSelect(article, target);
          }
        }}
      />
    );
  }

  return (
    <ToolButton
      title={intl.formatMessage({
        id: "labels.table-of-content",
      })}
      icon={<MenuOutlined />}
      content={tocWidget}
    />
  );
};

export default ToolButtonTocWidget;
