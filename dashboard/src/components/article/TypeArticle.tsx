import { useState } from "react";
import { Modal } from "antd";
import { ExclamationCircleOutlined } from "@ant-design/icons";
import { IArticleDataResponse } from "../api/Article";
import { ArticleMode, ArticleType } from "./Article";
import TypeArticleReader from "./TypeArticleReader";
import ArticleEdit from "./ArticleEdit";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  anthologyId?: string | null;
  active?: boolean;
  hideInteractive?: boolean;
  hideTitle?: boolean;
  isSubWindow?: boolean;
  onArticleChange?: Function;
  onArticleEdit?: Function;
  onLoad?: Function;
  onAnthologySelect?: Function;
}
const TypeArticleWidget = ({
  type,
  channelId,
  articleId,
  anthologyId,
  mode = "read",
  active = false,
  hideInteractive = false,
  hideTitle = false,
  isSubWindow = false,
  onArticleChange,
  onLoad,
  onAnthologySelect,
  onArticleEdit,
}: IWidget) => {
  const [edit, setEdit] = useState(false);
  return (
    <div>
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
          type={type}
          channelId={channelId}
          articleId={articleId}
          anthologyId={anthologyId}
          mode={mode}
          active={active}
          hideInteractive={hideInteractive}
          hideTitle={hideTitle}
          onArticleChange={(type: string, id: string, target: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target);
            }
          }}
          onLoad={(data: IArticleDataResponse) => {
            if (typeof onLoad !== "undefined") {
              onLoad(data);
            }
          }}
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

export default TypeArticleWidget;
