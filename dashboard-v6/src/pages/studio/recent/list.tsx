import { useEffect, useRef, useState } from "react";
import { ActionType } from "@ant-design/pro-components";

import { ArticleMode, ArticleType } from "../../../components/article/Article";
import { useAppSelector } from "../../../hooks";
import { currentUser as _currentUser } from "../../../reducers/current-user";
import ArticleDrawer from "../../../components/article/ArticleDrawer";
import RecentList from "../../../components/recent/RecentList";
import { fullUrl } from "../../../utils";

export interface IRecentRequest {
  type: ArticleType;
  article_id: string;
  param?: string;
}
interface IParam {
  book?: string;
  para?: string;
  channel?: string;
  mode?: string;
}
interface IRecentData {
  id: string;
  title: string;
  type: ArticleType;
  article_id: string;
  param: string | null;
  updated_at: string;
}

export interface IRecentResponse {
  ok: boolean;
  message: string;
  data: IRecentData;
}

interface IRecent {
  id: string;
  title: string;
  type: ArticleType;
  articleId: string;
  updatedAt: string;
  param?: IParam;
}
export interface IArticleParam {
  type: ArticleType;
  articleId: string;
  mode?: ArticleMode;
  channelId?: string;
  book?: string;
  para?: string;
}
const Widget = () => {
  const user = useAppSelector(_currentUser);
  const ref = useRef<ActionType>();
  const [articleOpen, setArticleOpen] = useState(false);
  const [param, setParam] = useState<IArticleParam>();

  useEffect(() => {
    ref.current?.reload();
  }, [user]);
  return (
    <>
      <RecentList
        onSelect={(
          event: React.MouseEvent<HTMLElement, MouseEvent>,
          param: IRecent
        ) => {
          if (event.ctrlKey || event.metaKey) {
            let url = `/article/${param.type}/${param.articleId}?mode=`;
            url += param.param?.mode ? param.param?.mode : "read";
            url += param.param?.channel
              ? `&channel=${param.param?.channel}`
              : "";
            url += param.param?.book ? `&book=${param.param?.book}` : "";
            url += param.param?.para ? `&par=${param.param?.para}` : "";

            window.open(fullUrl(url), "_blank");
          } else {
            setParam({
              type: param.type,
              articleId: param.articleId,
              mode: param.param?.mode as ArticleMode,
              channelId: param.param?.channel,
              book: param.param?.book,
              para: param.param?.para,
            });
            setArticleOpen(true);
          }
        }}
      />
      <ArticleDrawer
        {...param}
        open={articleOpen}
        onClose={() => setArticleOpen(false)}
      />
    </>
  );
};

export default Widget;
