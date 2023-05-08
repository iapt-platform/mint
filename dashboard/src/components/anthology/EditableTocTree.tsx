import { message } from "antd";
import { useEffect, useState } from "react";

import { get, put } from "../../request";
import {
  IArticleMapAddResponse,
  IArticleMapListResponse,
  IArticleMapUpdateRequest,
} from "../api/Article";
import EditableTree, { ListNodeData } from "../article/EditableTree";

interface IWidget {
  anthologyId?: string;
  onSelect?: Function;
}
const Widget = ({ anthologyId, onSelect }: IWidget) => {
  const [tocData, setTocData] = useState<ListNodeData[]>([]);

  useEffect(() => {
    get<IArticleMapListResponse>(
      `/v2/article-map?view=anthology&id=${anthologyId}`
    ).then((json) => {
      console.log("文集get", json);
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
      <EditableTree
        treeData={tocData}
        onChange={(data: ListNodeData[]) => {
          console.log("onChange", data);
        }}
        onSave={(data: ListNodeData[]) => {
          console.log("onSave", data);
          put<IArticleMapUpdateRequest, IArticleMapAddResponse>(
            `/v2/article-map/${anthologyId}`,
            {
              data: data.map((item) => {
                let title = "";
                if (typeof item.title === "string") {
                  title = item.title;
                }
                //TODO 整一个string title
                return {
                  article_id: item.key,
                  level: item.level,
                  title: title,
                  children: item.children,
                };
              }),
              operation: "anthology",
            }
          )
            .finally(() => {})
            .then((json) => {
              if (json.ok) {
                message.success(json.data);
              } else {
                message.error(json.message);
              }
            })
            .catch((e) => console.error(e));
        }}
      />
    </div>
  );
};

export default Widget;
