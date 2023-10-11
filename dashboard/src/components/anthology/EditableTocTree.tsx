import { Button, message } from "antd";
import { useEffect, useState } from "react";
import { FolderOpenOutlined } from "@ant-design/icons";

import { get as getUiLang } from "../../locales";

import { get, post, put } from "../../request";
import {
  IArticleCreateRequest,
  IArticleDataResponse,
  IArticleMapAddResponse,
  IArticleMapListResponse,
  IArticleMapRequest,
  IArticleMapUpdateRequest,
  IArticleResponse,
} from "../api/Article";
import ArticleListModal from "../article/ArticleListModal";
import EditableTree, {
  ListNodeData,
  TreeNodeData,
} from "../article/EditableTree";
import ArticleEditDrawer from "../article/ArticleEditDrawer";
import ArticleDrawer from "../article/ArticleDrawer";
import { fullUrl, randomString } from "../../utils";

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
  const [articleId, setArticleId] = useState<string>();
  const [openEditor, setOpenEditor] = useState(false);
  const [updatedArticle, setUpdatedArticle] = useState<TreeNodeData>();
  const [openViewer, setOpenViewer] = useState(false);
  const [viewArticleId, setViewArticleId] = useState<string>();

  const save = (data?: ListNodeData[]) => {
    console.log("onSave", data);
    if (typeof data === "undefined") {
      return;
    }
    const url = `/v2/article-map/${anthologyId}`;
    const newData: IArticleMapRequest[] = data.map((item) => {
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
        deleted_at: item.deletedAt,
      };
    });

    put<IArticleMapUpdateRequest, IArticleMapAddResponse>(url, {
      data: newData,
      operation: "anthology",
    })
      .finally(() => {})
      .then((json) => {
        if (json.ok) {
          message.success(json.data);
        } else {
          message.error(json.message);
        }
      })
      .catch((e) => console.error(e));
  };

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
            trigger={<Button icon={<FolderOpenOutlined />}>添加</Button>}
            multiple={false}
            onSelect={(id: string, title: string) => {
              console.log("add article", id);
              const newNode: TreeNodeData = {
                key: randomString(),
                id: id,
                title: title,
                children: [],
                level: 1,
              };
              setAddArticle(newNode);
            }}
          />
        }
        updatedNode={updatedArticle}
        onChange={(data: ListNodeData[]) => {
          save(data);
        }}
        onSave={(data: ListNodeData[]) => {
          save(data);
        }}
        onAppend={async (
          node: TreeNodeData
        ): Promise<TreeNodeData | undefined> => {
          if (typeof studioName === "undefined") {
            console.log("studio", studioName);
            return;
          }
          const res = await post<IArticleCreateRequest, IArticleResponse>(
            `/v2/article`,
            {
              title: "new article",
              lang: getUiLang(),
              studio: studioName,
              anthologyId: anthologyId,
            }
          );

          console.log(res);
          if (res.ok) {
            return {
              key: randomString(),
              id: res.data.uid,
              title: res.data.title,
              children: [],
              level: node.level + 1,
            };
          } else {
            return;
          }
        }}
        onNodeEdit={(key: string) => {
          setArticleId(key);
          setOpenEditor(true);
        }}
        onTitleClick={(
          e: React.MouseEvent<HTMLElement, MouseEvent>,
          node: TreeNodeData
        ) => {
          if (e.ctrlKey || e.metaKey) {
            window.open(fullUrl(`/article/article/${node.id}`), "_blank");
          } else {
            setViewArticleId(node.id);
            setOpenViewer(true);
          }
        }}
      />
      <ArticleEditDrawer
        articleId={articleId}
        open={openEditor}
        onClose={() => setOpenEditor(false)}
        onChange={(data: IArticleDataResponse) => {
          console.log("new title", data.title);
          setUpdatedArticle({
            key: randomString(),
            id: data.uid,
            title: data.title,
            level: 0,
            children: [],
          });
        }}
      />
      <ArticleDrawer
        articleId={viewArticleId}
        type="article"
        open={openViewer}
        onClose={() => setOpenViewer(false)}
      />
    </div>
  );
};

export default EditableTocTreeWidget;
