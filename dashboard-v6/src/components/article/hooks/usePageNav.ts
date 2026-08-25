// ─────────────────────────────────────────────
// usePageNav.ts
// ─────────────────────────────────────────────
/**
 * usePageNav
 *
 * 按「页码引用」解析并获取段落区间数据。
 *
 * pageId 形如 `M-dīghanikāya-2-10`（页码类型_书名_卷号_页码）。
 * 内部调用 fetchPageNav 请求 /api/v2/nav-page，并把返回的
 * curr.paragraph → next.paragraph 区间转换为 TypePali 需要的参数。
 *
 * @returns
 *   - nav        页码导航数据（curr/prev/next），失败时为 undefined
 *   - paramPali  可直接传给 TypePali 的段落参数，成功前为 undefined
 *   - loading    请求进行中
 *   - errorCode  HTTP 错误码，无错误时为 null
 *   - errorMessage 后端/网络错误信息
 */
// ─────────────────────────────────────────────

import { useState, useEffect } from "react";

import { fetchPageNav } from "../../../api/article";
import type { ArticleMode, IPageNavData } from "../../../api/article";
import { HttpError } from "../../../request";

export interface IPagePaliParam {
  id: string;
  book: string;
  para: string;
  mode: ArticleMode;
  channelId?: string;
}

interface IUsePageNavResult {
  nav?: IPageNavData;
  paramPali?: IPagePaliParam;
  loading: boolean;
  errorCode: number | null;
  errorMessage: string | null;
}

export const usePageNav = (
  pageId?: string,
  channelId?: string,
  mode: ArticleMode = "read"
): IUsePageNavResult => {
  const [nav, setNav] = useState<IPageNavData>();
  const [paramPali, setParamPali] = useState<IPagePaliParam>();
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  useEffect(() => {
    if (!pageId) return;
    const pageParam = pageId.split("_");
    if (pageParam.length < 4) return;

    let active = true;

    const fetchData = async () => {
      setLoading(true);
      setErrorCode(null);
      setErrorMessage(null);

      try {
        const res = await fetchPageNav(pageId);
        if (!active) return;

        if (!res.ok) {
          setErrorCode(400);
          setErrorMessage(res.message);
          return;
        }

        const data = res.data;
        setNav(data);

        const begin = data.curr.paragraph;
        const end = data.next.paragraph;
        const para: number[] = [];
        for (let index = begin; index <= end; index++) {
          para.push(index);
        }
        setParamPali({
          id: `${data.curr.book}-${data.curr.paragraph}`,
          book: data.curr.book.toString(),
          para: para.join(),
          mode: mode,
          channelId: channelId,
        });
      } catch (e) {
        console.error("page nav fetch", e);
        if (!active) return;
        if (e instanceof HttpError) {
          setErrorCode(e.status);
          setErrorMessage(e.message);
        } else {
          setErrorCode(0);
          setErrorMessage("Network error");
        }
      } finally {
        if (active) setLoading(false);
      }
    };

    fetchData();

    return () => {
      active = false;
    };
  }, [pageId, channelId, mode]);

  return { nav, paramPali, loading, errorCode, errorMessage };
};
