import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

import { get } from "../../request";
import { IArticleMapListResponse } from "../api/Article";
import { ListNodeData } from "../article/EditableTree";
import TocTree from "../article/TocTree";

interface IWidget {
  anthologyId?: string;
  onSelect?: Function;
  onArticleSelect?: Function;
}
const AnthologyTocTreeWidget = ({
  anthologyId,
  onSelect,
  onArticleSelect,
}: IWidget) => {
  const [tocData, setTocData] = useState<ListNodeData[]>([]);
  const [expandedKeys, setExpandedKeys] = useState<string[]>();

  useEffect(() => {
    if (typeof anthologyId === "undefined") {
      return;
    }
    const url = `/v2/article-map?view=anthology&id=${anthologyId}`;
    console.log("url", url);
    get<IArticleMapListResponse>(url).then((json) => {
      if (json.ok) {
        const toc: ListNodeData[] = json.data.rows.map((item) => {
          return {
            key: item.article_id ? item.article_id : item.title,
            title: item.title_text ? item.title_text : item.title,
            level: item.level,
            deletedAt: item.deleted_at,
          };
        });
        setTocData(toc);
        setExpandedKeys(
          json.data.rows
            .filter((value) => value.level === 1)
            .map((item) => (item.article_id ? item.article_id : item.title))
        );
      }
    });
  }, [anthologyId]);
  return (
    <TocTree
      treeData={tocData}
      expandedKeys={expandedKeys}
      onSelect={(keys: string[]) => {
        if (
          typeof onArticleSelect !== "undefined" &&
          typeof anthologyId !== "undefined"
        ) {
          onArticleSelect(anthologyId, keys);
        }
      }}
    />
  );
};

export default AnthologyTocTreeWidget;
