import { message } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import { IArticleResponse } from "../api/Article";
import ArticleView, { IWidgetArticleData } from "./ArticleView";

export type ArticleMode = "read" | "edit";
interface IWidgetArticle {
  type?: string;
  articleId?: string;
  mode?: ArticleMode;
  active?: boolean;
  showModeSwitch?: boolean;
  showMainMenu?: boolean;
  showContextMenu?: boolean;
}
const Widget = ({
  type,
  articleId,
  mode = "read",
  active = false,
  showModeSwitch = true,
  showMainMenu = true,
  showContextMenu = true,
}: IWidgetArticle) => {
  const [articleData, setArticleData] = useState<IWidgetArticleData>();

  useEffect(() => {
    if (!active) {
      return;
    }
    if (typeof type !== "undefined" && typeof articleId !== "undefined") {
      get<IArticleResponse>(`/v2/${type}/${articleId}/${mode}`).then((json) => {
        if (json.ok) {
          setArticleData(json.data);
        } else {
          message.error(json.message);
        }
      });
    }
  }, [active, type, articleId, mode]);
  return <ArticleView {...articleData} />;
};

export default Widget;
