import { useEffect, useState } from "react";

import { get } from "../../request";
import { IPageNavData, IPageNavResponse } from "../api/Article";

import { ArticleMode, ArticleType } from "./Article";
import "./article.css";
import { message } from "antd";

import { bookName } from "../fts/book_name";
import TypePali from "./TypePali";
import NavigateButton from "./NavigateButton";

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
  onArticleChange?: Function;
  onFinal?: Function;
  onLoad?: Function;
}
const TypeTermWidget = ({
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
  const [nav, setNav] = useState<IPageNavData>();

  useEffect(() => {
    if (typeof articleId === "undefined") {
      return;
    }

    const pageParam = articleId.split("_");
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
      });
  }, [articleId, channelId, mode]);

  return (
    <div>
      {paramPali ? (
        <>
          <TypePali
            type={"para"}
            {...paramPali}
            onArticleChange={(type: ArticleType, id: string) => {
              if (typeof onArticleChange !== "undefined") {
                onArticleChange(type, id);
              }
            }}
          />
          <NavigateButton
            prevTitle={nav?.prev.page.toString()}
            nextTitle={nav?.next.page.toString()}
            onNext={() => {
              if (typeof onArticleChange !== "undefined") {
                if (typeof articleId === "undefined") {
                  return;
                }
                const pageParam = articleId.split("_");
                if (pageParam.length < 4) {
                  return;
                }
                const id = `${pageParam[0]}-${pageParam[1]}-${pageParam[2]}-${
                  parseInt(pageParam[3]) + 1
                }`;
                onArticleChange("page", id);
              }
            }}
            onPrev={() => {
              if (typeof onArticleChange !== "undefined") {
                if (typeof articleId === "undefined") {
                  return;
                }
                const pageParam = articleId.split("_");
                if (pageParam.length < 4) {
                  return;
                }
                const id = `${pageParam[0]}-${pageParam[1]}-${pageParam[2]}-${
                  parseInt(pageParam[3]) - 1
                }`;
                onArticleChange("page", id);
              }
            }}
          />
        </>
      ) : (
        <>loading</>
      )}
    </div>
  );
};

export default TypeTermWidget;
