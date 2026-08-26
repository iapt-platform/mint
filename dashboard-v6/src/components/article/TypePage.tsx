import { useState } from "react";
import { Alert } from "antd";
import { useIntl } from "react-intl";

import type { ArticleMode, ArticleType } from "../../api/article";
import type { TTarget } from "../../types";
import { fullUrl } from "../../utils";
import TypePali from "./TypePali";
import NavigateButton from "./components/NavigateButton";
import ArticleSkeleton from "./components/ArticleSkeleton";
import ErrorResult from "../general/ErrorResult";
import { usePageNav } from "./hooks/usePageNav";

interface IWidget {
  articleId?: string;
  mode?: ArticleMode;
  channelId?: string | null;
  focus?: string | null;
  onArticleChange?: (type: ArticleType, id: string, target: TTarget) => void;
}

/**
 * 按「页码引用」渲染文章。
 *
 * articleId 形如 `M-dīghanikāya-2-10`（页码类型_书名_卷号_页码）。
 * 内部通过 usePageNav 解析页码并取回段落区间，再用 TypePali 渲染，
 * 底部提供上一页/下一页导航。
 */
const TypePageWidget = ({
  articleId,
  mode = "read",
  channelId,
  focus,
  onArticleChange,
}: IWidget) => {
  const intl = useIntl();
  const [currId, setCurrId] = useState(articleId);

  // 渲染期派生：articleId prop 变化时同步 currId（React 官方推荐的派生 state 模式）
  const [prevArticleId, setPrevArticleId] = useState(articleId);
  if (prevArticleId !== articleId) {
    setPrevArticleId(articleId);
    setCurrId(articleId);
  }

  const { nav, paramPali, errorCode, errorMessage } = usePageNav(
    currId,
    channelId ?? undefined,
    mode
  );

  let pageInfo: string | undefined;
  if (currId) {
    const pageParam = currId.split("_");
    if (pageParam.length >= 4) {
      pageInfo =
        `版本：` +
        intl.formatMessage({
          id: `labels.page.number.type.` + pageParam[0].toUpperCase(),
        }) +
        ` 书名：${pageParam[1]} 卷号：${pageParam[2]} 页码：${pageParam[3]}`;
    }
  }

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
    const target: TTarget =
      event.ctrlKey || event.metaKey ? "_blank" : "_self";
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
            type="para"
            hideNav
            {...paramPali}
            focus={focus}
            onArticleChange={(type, id, target) => {
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
      ) : errorCode !== null ? (
        <ErrorResult code={errorCode} message={errorMessage ?? undefined} />
      ) : (
        <ArticleSkeleton />
      )}
    </div>
  );
};

export default TypePageWidget;
