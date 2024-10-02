import { Button, message } from "antd";
import { useEffect, useState } from "react";
import { FolderOpenOutlined } from "@ant-design/icons";

import { get as getUiLang } from "../../locales";

import { get, post, put } from "../../request";
import {
  IAnthologyDataResponse,
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
import ArticleDrawer from "../article/ArticleDrawer";
import { fullUrl, randomString } from "../../utils";

interface IWidget {
  anthologyId?: string;
  studioName?: string;
  myStudioName?: string;
  anthology?: IAnthologyDataResponse;
  onSelect?: Function;
}
const EditableTocTreeWidget = ({
  anthologyId,
  anthology,
  studioName,
  myStudioName,
  onSelect,
}: IWidget) => {
  const [tocData, setTocData] = useState<ListNodeData[]>([]);
  const [addArticle, setAddArticle] = useState<TreeNodeData>();
  const [updatedArticle, setUpdatedArticle] = useState<TreeNodeData>();
  const [openViewer, setOpenViewer] = useState(false);
  const [viewArticle, setViewArticle] = useState<TreeNodeData>();

  const save = (data?: ListNodeData[]) => {
    console.debug("onSave", data);
    if (typeof data === "undefined") {
      console.warn("data === undefined");
      return;
    }
    const url = `/v2/article-map/${anthologyId}`;
    console.info("url", url);
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
        status: item.status,
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
      .catch((e) => message.error(e));
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
            title_text: item.title_text ? item.title_text : item.title,
            level: item.level,
            status: item.status,
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
            studioName={myStudioName}
            trigger={<Button icon={<FolderOpenOutlined />}>添加</Button>}
            multiple={false}
            onSelect={(id: string, title: string) => {
              console.log("add article", id);
              const newNode: TreeNodeData = {
                key: randomString(),
                id: id,
                title: title,
                title_text: title,
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
          /**
           * 在某节点下append新的节点
           */
          if (typeof studioName === "undefined") {
            console.log("studio", studioName);
            return;
          }
          const res = await post<IArticleCreateRequest, IArticleResponse>(
            `/v2/article`,
            {
              title: "new article",
              lang: anthology?.lang ?? getUiLang(),
              studio: studioName,
              anthologyId: anthologyId,
              status: anthology?.status ?? undefined,
            }
          );

          console.log(res);
          if (res.ok) {
            return {
              key: randomString(),
              id: res.data.uid,
              title: res.data.title,
              title_text: res.data.title,
              children: [],
              level: node.level + 1,
            };
          } else {
            return;
          }
        }}
        onTitleClick={(
          e: React.MouseEvent<HTMLElement, MouseEvent>,
          node: TreeNodeData
        ) => {
          if (e.ctrlKey || e.metaKey) {
            window.open(fullUrl(`/article/article/${node.id}`), "_blank");
          } else {
            setViewArticle(node);
            setOpenViewer(true);
          }
        }}
      />
      <ArticleDrawer
        articleId={viewArticle?.id}
        anthologyId={anthologyId}
        type="article"
        open={openViewer}
        title={viewArticle?.title_text}
        onClose={() => setOpenViewer(false)}
        onArticleEdit={(value: IArticleDataResponse) => {
          setUpdatedArticle({
            key: randomString(),
            id: value.uid,
            title: value.title,
            title_text: value.title_text,
            level: 0,
            children: [],
          });
        }}
        onTitleChange={(value: string) => {
          if (viewArticle?.id) {
            setUpdatedArticle({
              key: randomString(),
              id: viewArticle?.id,
              title: value,
              title_text: value,
              level: 0,
              children: [],
            });
          }
        }}
      />
    </div>
  );
};

export default EditableTocTreeWidget;
