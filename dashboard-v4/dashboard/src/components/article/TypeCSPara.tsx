import { useEffect, useState } from "react";
import { Alert, message } from "antd";
import { useIntl } from "react-intl";

import { get } from "../../request";
import { ICSParaNavData, ICSParaNavResponse } from "../api/Article";
import { ArticleMode, ArticleType } from "./Article";
import TypePali from "./TypePali";
import NavigateButton from "./NavigateButton";
import ArticleSkeleton from "./ArticleSkeleton";
import ErrorResult from "../general/ErrorResult";
import "./article.css";
import { ISearchParams } from "../../pages/library/article/show";

interface IParam {
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  book?: string | null;
  para?: string | null;
}
interface IWidget {
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  onArticleChange?: (
    type: ArticleType,
    id: string,
    target: string,
    param?: ISearchParams[]
  ) => void;
  onFinal?: Function;
  onLoad?: Function;
}
const TypeCSParaWidget = ({
  channelId,
  articleId,
  mode = "read",
  onArticleChange,
}: IWidget) => {
  /**
   * 页面加载
   * M 缅文页码
   * P PTS页码
   * V vri页码
   * T 泰文页码
   * O 其他
   * para 缅文段落号
   * url 格式 /article/page/M-dīghanikāya-2-10
   * 书名在 dashboard\src\components\fts\book_name.ts
   */

  const [paramPali, setParamPali] = useState<IParam>();
  const [nav, setNav] = useState<ICSParaNavData>();
  const [errorCode, setErrorCode] = useState<number>();
  const [errorMessage, setErrorMessage] = useState<string>();
  const [pageInfo, setPageInfo] = useState<string>();
  const intl = useIntl();

  useEffect(() => {
    if (typeof articleId === "undefined") {
      console.error("articleId 不能为空");
      return;
    }

    const pageParam = articleId.split("_");
    if (pageParam.length !== 3) {
      console.error("pageParam 必须为三个");
      return;
    }

    const url = `/v2/nav-cs-para/${articleId}`;
    setPageInfo("");
    console.log("url", url);
    get<ICSParaNavResponse>(url)
      .then((json) => {
        if (json.ok) {
          const data = json.data;
          setNav(data);
          const begin = data.curr.start;
          const end = data.end;
          let para: number[] = [];
          for (let index = begin; index <= end; index++) {
            para.push(index);
          }
          setParamPali({
            articleId: `${data.curr.book}-${data.curr.start}`,
            book: data.curr.book.toString(),
            para: para.join(),
            mode: mode,
            channelId: channelId,
          });
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {})
      .catch((e) => {
        console.error(e);
        setErrorCode(e);
        if (e === 404) {
          setErrorMessage(`该页面不存在。页面信息：${pageInfo}`);
        }
      });
  }, [articleId, channelId, mode, pageInfo]);

  return (
    <div>
      {pageInfo ? <Alert message={pageInfo} type="info" closable /> : undefined}
      {paramPali ? (
        <>
          <TypePali
            type={"para"}
            hideNav
            {...paramPali}
            onArticleChange={(
              type: ArticleType,
              id: string,
              target: string,
              param?: ISearchParams[] | undefined
            ) => {
              if (typeof onArticleChange !== "undefined") {
                onArticleChange(type, id, target, param);
              }
            }}
          />
          <NavigateButton
            prevTitle={nav?.prev?.content.slice(0, 10)}
            nextTitle={nav?.next?.content.slice(0, 10)}
            onNext={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
              if (typeof onArticleChange !== "undefined") {
                if (typeof articleId === "undefined") {
                  return;
                }
                const pageParam = articleId.split("_");
                if (pageParam.length !== 3) {
                  return;
                }
                const id = `${pageParam[0]}-${pageParam[1]}-${
                  parseInt(pageParam[2]) + 1
                }`;
                let target = "_self";
                if (event.ctrlKey || event.metaKey) {
                  target = "_blank";
                }
                onArticleChange("cs-para", id, target);
              }
            }}
            onPrev={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
              if (typeof onArticleChange !== "undefined") {
                if (typeof articleId === "undefined") {
                  return;
                }
                const pageParam = articleId.split("_");
                if (pageParam.length < 3) {
                  return;
                }
                const id = `${pageParam[0]}-${pageParam[1]}-${
                  parseInt(pageParam[2]) - 1
                }`;
                let target = "_self";
                if (event.ctrlKey || event.metaKey) {
                  target = "_blank";
                }
                onArticleChange("cs-para", id, target);
              }
            }}
          />
        </>
      ) : errorCode ? (
        <ErrorResult code={errorCode} message={errorMessage} />
      ) : (
        <ArticleSkeleton />
      )}
    </div>
  );
};

export default TypeCSParaWidget;
