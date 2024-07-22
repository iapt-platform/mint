import { useEffect, useState } from "react";
import { Alert, message } from "antd";
import { useIntl } from "react-intl";

import { get } from "../../request";
import { IPageNavData, IPageNavResponse } from "../api/Article";
import { ArticleMode, ArticleType } from "./Article";
import { bookName } from "../fts/book_name";
import TypePali from "./TypePali";
import NavigateButton from "./NavigateButton";
import ArticleSkeleton from "./ArticleSkeleton";
import ErrorResult from "../general/ErrorResult";
import "./article.css";
import { fullUrl } from "../../utils";
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
  focus?: string | null;
  onArticleChange?: (
    type: ArticleType,
    id: string,
    target: string,
    param?: ISearchParams[]
  ) => void;
  onFinal?: Function;
  onLoad?: Function;
}
const TypePageWidget = ({
  channelId,
  articleId,
  focus,
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
  const [nav, setNav] = useState<IPageNavData>();
  const [errorCode, setErrorCode] = useState<number>();
  const [errorMessage, setErrorMessage] = useState<string>();
  const [pageInfo, setPageInfo] = useState<string>();
  const [currId, setCurrId] = useState(articleId);

  const intl = useIntl();

  useEffect(() => setCurrId(articleId), [articleId]);

  useEffect(() => {
    if (typeof currId === "undefined") {
      return;
    }

    const pageParam = currId.split("_");
    if (pageParam.length < 4) {
      return;
    }
    //查询书号
    const booksId = bookName
      .filter((value) => value.term === pageParam[1])
      .map((item) => item.id)
      .join("_");
    const url = `/v2/nav-page/${pageParam[0].toUpperCase()}-${booksId}-${
      pageParam[2]
    }-${pageParam[3]}`;
    setPageInfo(
      `版本：` +
        intl.formatMessage({
          id: `labels.page.number.type.` + pageParam[0].toUpperCase(),
        }) +
        ` 书名：${pageParam[1]} 卷号：${pageParam[2]} 页码：${pageParam[3]}`
    );
    console.log("url", url);
    get<IPageNavResponse>(url)
      .then((json) => {
        if (json.ok) {
          const data = json.data;
          setNav(data);
          const begin = data.curr.paragraph;
          const end = data.next.paragraph;
          let para: number[] = [];
          for (let index = begin; index <= end; index++) {
            para.push(index);
          }
          setParamPali({
            articleId: `${data.curr.book}-${data.curr.paragraph}`,
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
  }, [currId, channelId, intl, mode, pageInfo]);

  const seek = (
    event: React.MouseEvent<HTMLElement, MouseEvent>,
    page: number
  ) => {
    if (typeof currId === "undefined") {
      return;
    }
    const pageParam = currId.split("_");
    if (pageParam.length < 4) {
      return;
    }
    const id = `${pageParam[0]}_${pageParam[1]}_${pageParam[2]}_${
      parseInt(pageParam[3]) + page
    }`;
    let target = "_self";
    if (event.ctrlKey || event.metaKey) {
      target = "_blank";
    }
    if (typeof onArticleChange !== "undefined") {
      onArticleChange("page", id, target);
    } else {
      if (target === "_blank") {
        let url = `/article/page/${id}?mode=${mode}`;
        if (channelId) {
          url += `&channel=${channelId}`;
        }
        window.open(fullUrl(url), "_blank");
      } else {
        setCurrId(id);
      }
    }
  };

  return (
    <div>
      {pageInfo ? <Alert message={pageInfo} type="info" closable /> : undefined}
      {paramPali ? (
        <>
          <TypePali
            type={"para"}
            hideNav
            {...paramPali}
            focus={focus}
            onArticleChange={(
              type: ArticleType,
              id: string,
              target: string
            ) => {
              if (typeof onArticleChange !== "undefined") {
                onArticleChange(type, id, target);
              }
            }}
          />
          <NavigateButton
            prevTitle={nav?.prev.page.toString()}
            nextTitle={nav?.next.page.toString()}
            onNext={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
              seek(event, 1);
            }}
            onPrev={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
              seek(event, -1);
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

export default TypePageWidget;
