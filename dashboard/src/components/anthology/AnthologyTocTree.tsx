import { useEffect, useState } from "react";

import { get } from "../../request";
import { IArticleMapListResponse } from "../api/Article";
import { ListNodeData } from "../article/EditableTree";
import TocTree from "../article/TocTree";

interface IWidget {
  anthologyId?: string;
  channels?: string[];
  onSelect?: Function;
  onClick?: Function;
  onArticleSelect?: Function;
}
const AnthologyTocTreeWidget = ({
  anthologyId,
  channels,
  onSelect,
  onClick,
  onArticleSelect,
}: IWidget) => {
  const [tocData, setTocData] = useState<ListNodeData[]>([]);
  const [expandedKeys, setExpandedKeys] = useState<string[]>();

  useEffect(() => {
    if (typeof anthologyId === "undefined") {
      return;
    }
    let url = `/v2/article-map?view=anthology&id=${anthologyId}&lazy=1`;
    url += channels && channels.length > 0 ? "&channel=" + channels[0] : "";
    console.log("url", url);
    get<IArticleMapListResponse>(url).then((json) => {
      if (json.ok) {
        const toc: ListNodeData[] = json.data.rows.map((item) => {
          return {
            key: item.article_id ? item.article_id : item.title,
            title: item.title_text ? item.title_text : item.title,
            level: item.level,
            children: item.children,
            status: item.status,
            deletedAt: item.deleted_at,
          };
        });
        setTocData(toc);
        if (json.data.rows.length === json.data.count) {
          setExpandedKeys(
            json.data.rows
              .filter((value) => value.level === 1)
              .map((item) => (item.article_id ? item.article_id : item.title))
          );
        } else {
          setExpandedKeys(undefined);
        }
      }
    });
  }, [anthologyId, channels]);
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
      onClick={(
        id: string,
        e: React.MouseEvent<HTMLSpanElement, MouseEvent>
      ) => {
        const target = e.ctrlKey || e.metaKey ? "_blank" : "self";
        if (
          typeof onClick !== "undefined" &&
          typeof anthologyId !== "undefined"
        ) {
          onClick(anthologyId, id, target);
        }
      }}
    />
  );
};

export default AnthologyTocTreeWidget;
