import { Button, message } from "antd";
import { useEffect, useState } from "react";
import { FileAddOutlined } from "@ant-design/icons";

import { get, put } from "../../request";
import {
  IArticleMapAddResponse,
  IArticleMapListResponse,
  IArticleMapUpdateRequest,
} from "../api/Article";
import ArticleListModal from "../article/ArticleListModal";
import EditableTree, {
  ListNodeData,
  TreeNodeData,
} from "../article/EditableTree";

interface IWidget {
  anthologyId?: string;
  studioName?: string;
  onSelect?: Function;
}
const EditableTocTreeWidget = ({
  anthologyId,
  studioName,
  onSelect,
}: IWidget) => {
  const [tocData, setTocData] = useState<ListNodeData[]>([]);
  const [addArticle, setAddArticle] = useState<TreeNodeData>();
  useEffect(() => {
    get<IArticleMapListResponse>(
      `/v2/article-map?view=anthology&id=${anthologyId}`
    ).then((json) => {
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
        addOnArticle={addArticle}
        addFileButton={
          <ArticleListModal
            studioName={studioName}
            trigger={<Button icon={<FileAddOutlined />}>添加</Button>}
            multiple={false}
            onSelect={(id: string, title: string) => {
              console.log("add article", id);
              const newNode: TreeNodeData = {
                key: id,
                title: title,
                children: [],
                level: 1,
              };
              setAddArticle(newNode);
            }}
          />
        }
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

export default EditableTocTreeWidget;
