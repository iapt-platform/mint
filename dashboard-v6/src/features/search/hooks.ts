import { useEffect, useState } from "react";
import {
  aggregateWordBooks,
  searchTitles,
  searchPageNumbers,
  searchParagraphs,
  searchWordParagraphs,
  type BoldScope,
  type TitleHit,
  type TipitakaLayer,
  type PageNumberHit,
  type SearchHit,
  type SearchMeta,
  type SearchMode,
  type WordBook,
  type WordBookMeta,
  type WordParagraph,
  type WordParagraphMeta,
} from "../../api/tipitaka-search";
import { fetchWordForms, type WordFormGroup } from "../../api/pali-word-form";
import type { ApiError } from "../../api/error";

export const FULLTEXT_PER_PAGE = 20;
export const TITLE_PER_PAGE = 20;
export const DEFINITION_PER_PAGE = 20;

export interface IRemote<T, M> {
  loading: boolean;
  data?: T[];
  meta?: M;
  error?: ApiError;
}

/**
 * 发请求、丢弃过期响应的通用部分：条件变了，旧请求晚到的结果不能覆盖新的。
 * `key` 为 null 时不查（没有检索词）。
 */
function useRemote<T, M>(
  key: string | null,
  load: () => Promise<{ data?: T[]; meta?: M }>,
): IRemote<T, M> {
  const [state, setState] = useState<IRemote<T, M>>({ loading: false });

  useEffect(() => {
    if (key === null) {
      setState({ loading: false });
      return;
    }
    let stale = false;
    setState((s) => ({ ...s, loading: true, error: undefined }));
    load()
      .then((res) => {
        if (!stale)
          setState({ loading: false, data: res.data ?? [], meta: res.meta });
      })
      .catch((e: ApiError) => {
        if (!stale) setState({ loading: false, error: e });
      });
    return () => {
      stale = true;
    };
    // load 由 key 完全决定，不放进依赖
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [key]);

  return state;
}

export const useFullTextSearch = (
  q: string,
  mode: SearchMode,
  category: string[],
  page: number,
) => {
  const term = q.trim();
  const params = { q: term, mode, category, page, perPage: FULLTEXT_PER_PAGE };
  return useRemote<SearchHit, SearchMeta>(
    term ? JSON.stringify(params) : null,
    () => searchParagraphs(params),
  );
};

export const useTitleSearch = (
  q: string,
  layer: TipitakaLayer | undefined,
  page: number,
) => {
  const term = q.trim();
  const params = { q: term, layer, page, perPage: TITLE_PER_PAGE };
  return useRemote<TitleHit, { total?: number }>(
    term ? JSON.stringify(params) : null,
    () => searchTitles(params),
  );
};

/** 定义：检索词展开成词形（只支持单个词） */
export const useWordForms = (q: string) => {
  const term = q.trim();
  return useRemote<WordFormGroup, undefined>(
    term && !/\s/.test(term) ? term : null,
    () => fetchWordForms(term, { quiet: true }).then((data) => ({ data })),
  );
};

/**
 * 定义：命中这些词形的段落。
 * `bookIds` 为 null 表示书的范围还没算出来（目录过滤要等按书统计的结果），先不查。
 */
export const useWordParagraphs = (
  words: string[],
  bold: BoldScope | undefined,
  bookIds: number[] | null,
  page: number,
) => {
  const params = {
    words,
    bold,
    bookIds: bookIds ?? [],
    page,
    perPage: DEFINITION_PER_PAGE,
  };
  return useRemote<WordParagraph, WordParagraphMeta>(
    words.length && bookIds !== null ? JSON.stringify(params) : null,
    () => searchWordParagraphs(params),
  );
};

/** 定义：命中按书统计 */
export const useWordBooks = (words: string[], bold: BoldScope | undefined) => {
  const params = { words, bold };
  return useRemote<WordBook, WordBookMeta>(
    words.length ? JSON.stringify(params) : null,
    () => aggregateWordBooks(params),
  );
};

/** 页码：检索词是「页」或「卷.页」时才查 */
export const usePageNumbers = (
  query: { page: number; volume?: number } | null,
) =>
  useRemote<PageNumberHit, { total?: number }>(
    query ? JSON.stringify(query) : null,
    () =>
      query
        ? searchPageNumbers(query.page, query.volume)
        : Promise.resolve({ data: [] }),
  );
