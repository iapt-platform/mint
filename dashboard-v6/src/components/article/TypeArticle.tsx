import React, { useState } from "react";
import { Modal } from "antd";
import { ExclamationCircleOutlined } from "@ant-design/icons";
import type {
  ArticleMode,
  ArticleType,
  IArticleDataResponse,
} from "../../api/Article";

import TypeArticleReader from "./ArticleReader";
import ArticleEdit from "./ArticleEdit";
import type { TTarget } from "../../types";

interface IWidget {
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  parentChannels?: string[];
  anthologyId?: string | null;
  active?: boolean;
  hideInteractive?: boolean;
  hideTitle?: boolean;
  isSubWindow?: boolean;
  headerExtra?: React.ReactNode;
  onArticleChange?: (type: ArticleType, id: string, target?: TTarget) => void;
  onArticleEdit?: (value: IArticleDataResponse) => void;
  onLoad?: (data: IArticleDataResponse) => void;
  onAnthologySelect?: (
    id: string,
    e: React.MouseEvent<HTMLElement, MouseEvent>
  ) => void;
}
const TypeArticle = ({
  channelId,
  parentChannels,
  articleId,
  anthologyId,
  mode = "read",
  headerExtra,
  active = false,
  hideInteractive = false,
  hideTitle = false,
  isSubWindow = false,
  onArticleChange,
  onAnthologySelect,
  onArticleEdit,
}: IWidget) => {
  const [edit, setEdit] = useState(false);
  return (
    <div>
      {headerExtra}
      {edit ? (
        <ArticleEdit
          anthologyId={anthologyId ? anthologyId : undefined}
          articleId={articleId}
          resetButton="cancel"
          onSubmit={(value: IArticleDataResponse) => {
            if (typeof onArticleEdit !== "undefined") {
              onArticleEdit(value);
            }
            setEdit(false);
          }}
          onCancel={() => {
            Modal.confirm({
              icon: <ExclamationCircleOutlined />,
              content: "放弃修改吗？",
              okType: "danger",
              onOk() {
                setEdit(false);
              },
            });
          }}
        />
      ) : (
        <TypeArticleReader
          isSubWindow={isSubWindow}
          channelId={channelId}
          parentChannels={parentChannels}
          articleId={articleId}
          anthologyId={anthologyId}
          mode={mode}
          active={active}
          hideInteractive={hideInteractive}
          hideTitle={hideTitle}
          onArticleChange={onArticleChange}
          onAnthologySelect={(
            id: string,
            e: React.MouseEvent<HTMLElement, MouseEvent>
          ) => {
            if (typeof onAnthologySelect !== "undefined") {
              onAnthologySelect(id, e);
            }
          }}
          onEdit={() => {
            setEdit(true);
          }}
        />
      )}
    </div>
  );
};

export default TypeArticle;
