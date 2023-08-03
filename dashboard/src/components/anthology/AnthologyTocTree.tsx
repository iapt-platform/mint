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
            title: item.title,
            level: item.level,
            deletedAt: item.deleted_at,
          };
        });
        setTocData(toc);
      }
    });
  }, [anthologyId]);
  return (
    <div>
      <TocTree
        treeData={tocData}
        onSelect={(keys: string[]) => {
          if (
            typeof onArticleSelect !== "undefined" &&
            typeof anthologyId !== "undefined"
          ) {
            onArticleSelect(anthologyId, keys);
          }
        }}
      />
    </div>
  );
};

export default AnthologyTocTreeWidget;
