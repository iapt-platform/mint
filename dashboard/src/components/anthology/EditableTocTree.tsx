import { message } from "antd";
import { Key } from "antd/lib/table/interface";
import { useEffect, useState } from "react";

import { get, post, put } from "../../request";
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
  const [keys, setKeys] = useState<Key[]>();

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
        onSelect={(selectedKeys: Key[]) => {
          setKeys(selectedKeys);
        }}
      />
    </div>
  );
};

export default Widget;
