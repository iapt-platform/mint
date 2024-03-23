import { useState } from "react";
import { Alert, Button } from "antd";

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
  onArticleChange?: Function;
  onArticleEdit?: Function;
  onFinal?: Function;
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
  onArticleChange,
  onFinal,
  onLoad,
  onAnthologySelect,
  onArticleEdit,
}: IWidget) => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [edit, setEdit] = useState(false);
  return (
    <div>
      <div>
        {articleData?.role && articleData?.role !== "reader" && edit ? (
          <Alert
            message={"请在提交修改后点完成按钮"}
            type="info"
            action={<Button onClick={() => setEdit(!edit)}>{"完成"}</Button>}
          />
        ) : (
          <></>
        )}
      </div>
      {edit ? (
        <ArticleEdit
          anthologyId={anthologyId ? anthologyId : undefined}
          articleId={articleId}
          onChange={(value: IArticleDataResponse) => {
            if (typeof onArticleEdit !== "undefined") {
              onArticleEdit(value);
            }
          }}
        />
      ) : (
        <TypeArticleReader
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
            setArticleData(data);
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
