// src/features/TypePaliWidget.tsx

import { Divider, Dropdown, Button, Space, Tag } from "antd";
import { MoreOutlined } from "@ant-design/icons";
import { useEffect, type ReactNode } from "react";
import type {
  ArticleMode,
  ArticleType,
  IArticleDataResponse,
} from "../../api/article";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import store from "../../store";
import { refresh as focusRefresh } from "../../reducers/focus";
import useTipitaka from "../../hooks/useTipitaka";

import ArticleLayout from "./components/ArticleLayout";

import { useState } from "react";
import type { ITocPathNode } from "../../api/pali-text";
import TocTree from "./components/TocTree";
import PaliText from "../general/PaliText";
import Navigate from "./components/Navigate";
import TplBuilder from "../tpl-builder/TplBuilder";
import ArticleHeader from "./components/ArticleHeader";
import { TaskBuilderChapterModal } from "../task/TaskBuilderChapterModal";
import type { TTarget } from "../../types";
import TocPath from "../tipitaka/TocPath";
import ParagraphNode from "../tipitaka/ParagraphNode";
import "./article.css";

import { useIntl } from "react-intl";

export interface ISearchParams {
  key: string;
  value: string;
}

interface IWidget {
  headerExtra?: ReactNode;
  type?: ArticleType;
  id?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  book?: string | null;
  para?: string | null;
  active?: boolean;
  focus?: string | null;
  hideNav?: boolean;
  hideTitle?: boolean;
  hideHead?: boolean;
  onArticleChange?: (
    type: ArticleType,
    id: string,
    target: TTarget,
    param?: ISearchParams[],
  ) => void;
  onLoad?: (data: IArticleDataResponse) => void;
  onTitle?: (title: string) => void;
}

const TypePali = ({
  headerExtra,
  type,
  id,
  mode = "read",
  channelId,
  book,
  para,
  active = true,
  focus,
  hideNav = false,
  hideTitle = false,
  hideHead = false,
  onArticleChange,
}: IWidget) => {
  const intl = useIntl();

  const user = useAppSelector(currentUser);
  const channels = channelId?.split("_");

  const [taskBuilderModalOpen, setTaskBuilderModalOpen] = useState(false);
  const [tplOpen, setTplOpen] = useState(false);

  const {
    articleData,
    nodeData,
    toc,
    loading,
    errorCode,
    remains,
    loadNextChunk,
  } = useTipitaka({ type, id, mode, channelId, book, para, active });

  // focus 副作用保留在 feature 层，因为它与 Redux store 耦合属于业务交互
  useEffect(() => {
    const parts = focus?.split("-");
    if (parts?.length === 2) {
      store.dispatch(focusRefresh({ type: "para", id: focus }));
    } else if (parts?.length === 4) {
      store.dispatch(focusRefresh({ type: "sentence", id: focus }));
    }
  }, [focus]);

  // 派生展示数据
  let title = "";
  if (articleData) {
    title = articleData.title_text ?? articleData.title;
    if (type === "para" && id) {
      const [, para] = id.split("-");
      if (para) {
        title = title + "-" + para;
      }
    }
  }

  let mBook = "0",
    mPara = "0";
  if (typeof id === "string") {
    [mBook, mPara] = id.split("-");
  }

  let fullPath: ITocPathNode[] = [];
  if (articleData?.path && articleData.path.length > 0) {
    const currNode: ITocPathNode = {
      book: parseInt(mBook),
      paragraph: parseInt(mPara),
      title: title ?? "",
      level: articleData.path[articleData.path.length - 1].level + 1,
    };
    fullPath = [...articleData.path, currNode];
  }

  const handlePathChange = (
    node: ITocPathNode,
    e: React.MouseEvent<HTMLSpanElement | HTMLAnchorElement, MouseEvent>,
  ) => {
    let newType = type;
    let newArticle = "";
    if (node.level === 0) {
      newType = "series";
      newArticle = node.title;
    } else {
      newType = "chapter";
      newArticle = node.key ? node.key : `${node.book}-${node.paragraph}`;
    }
    const target = e.ctrlKey || e.metaKey ? "_blank" : "_self";
    onArticleChange?.(newType, newArticle, target);
  };

  return (
    <div className="pcd_article">
      <TaskBuilderChapterModal
        studioName={user?.realName}
        book={parseInt(mBook ?? "0")}
        para={parseInt(mPara ?? "0")}
        channels={channels}
        open={taskBuilderModalOpen}
        onClose={() => setTaskBuilderModalOpen(false)}
      />
      <TplBuilder
        title={title}
        tpl="chapter"
        articleId={id}
        channelsId={channelId}
        open={tplOpen}
        onClose={() => setTplOpen(false)}
      />

      <div></div>
      <ArticleHeader
        header={
          <Space>
            <>{headerExtra}</>
            <TocPath
              data={fullPath}
              channels={channels}
              onChange={handlePathChange}
            />
          </Space>
        }
        action={
          <Dropdown
            menu={{
              items: [
                { key: "tpl", label: "获取模板" },
                { key: "task", label: "生成任务" },
                {
                  key: "library",
                  label: intl.formatMessage({ id: "buttons.open.in.library" }),
                },
              ],
              onClick: ({ key }) => {
                if (key === "task") setTaskBuilderModalOpen(true);
                if (key === "tpl") setTplOpen(true);
                if (key === "library" && channels) {
                  window.open(
                    import.meta.env.VITE_APP_API_SERVER +
                      `/library/tipitaka/${id}/read?channel=${channels[0]}`,
                    "_blank",
                  );
                }
              },
            }}
            placement="bottomRight"
          >
            <Button shape="circle" size="small" icon={<MoreOutlined />} />
          </Dropdown>
        }
      />
      <ArticleLayout
        title={title}
        subTitle={articleData?.subtitle}
        summary={articleData?.summary}
        nodes={nodeData.map((item) => {
          return (
            <ParagraphNode key={`${item.book}-${item.para}`} initData={item} />
          );
        })}
        html={
          nodeData.length === 0
            ? articleData?.content
              ? [articleData?.content]
              : [""]
            : [""]
        }
        loading={loading}
        errorCode={errorCode}
        remains={remains}
        hideTitle={hideTitle}
        hideHead={hideHead}
        onEnd={() => {
          if (type === "chapter") loadNextChunk();
        }}
      />

      <Divider />

      <TocTree
        treeData={toc?.map((item) => {
          const strTitle = item.title ?? item.pali_title;
          const key = item.key ?? `${item.book}-${item.paragraph}`;
          const progress = item.progress?.map((p, id) => (
            <Tag key={id}>{Math.round(p * 100) + "%"}</Tag>
          ));
          return {
            key,
            title: (
              <Space>
                <PaliText text={strTitle === "" ? "[unnamed]" : strTitle} />
                {progress}
              </Space>
            ),
            level: item.level,
          };
        })}
        onClick={(
          id: string,
          e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
        ) => {
          if (e.ctrlKey || e.metaKey) {
            onArticleChange?.("chapter", id, "_blank");
          } else {
            onArticleChange?.("chapter", id, "_self");
          }
        }}
      />

      {!hideNav && (
        <>
          <Divider />
          <Navigate
            type={type}
            articleId={id}
            path={fullPath}
            onPathChange={(key: string) => {
              const node = articleData?.path?.find((v) => v.title === key);
              if (node) {
                const newType = node.level === 0 ? "series" : "chapter";
                const newArticle = node.key ?? `${node.book}-${node.paragraph}`;
                onArticleChange?.(newType, newArticle, "_self");
              }
            }}
            onChange={(
              event: React.MouseEvent<HTMLElement, MouseEvent>,
              newId: string,
            ) => {
              const target =
                event.ctrlKey || event.metaKey ? "_blank" : "_self";
              let param: ISearchParams[] = [];
              if (type === "para" && newId?.split("-").length > 1) {
                param = [{ key: "par", value: newId.split("-")[1] }];
              }
              onArticleChange?.(type as ArticleType, newId, target, param);
            }}
          />
        </>
      )}
    </div>
  );
};

export default TypePali;
