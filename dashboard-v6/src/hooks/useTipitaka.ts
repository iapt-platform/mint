// src/hooks/useTipitaka.ts

import { useEffect, useState, useCallback, useRef } from "react";
import type {
  ArticleMode,
  ArticleType,
  IArticleDataResponse,
  IChapterToc,
} from "../api/article";
import { fetchChapter, fetchNextParaChunk, fetchPara } from "../api/pali-text";
import type { IParagraphProps } from "../components/template/Paragraph";

interface IUseTipitakaProps {
  type?: ArticleType;
  id?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  book?: string | null;
  para?: string | null;
  active?: boolean;
}

interface IUseTipitakaReturn {
  articleData: IArticleDataResponse | undefined;
  articleHtml: string[];
  nodeData: IParagraphProps[];
  toc: IChapterToc[] | undefined;
  loading: boolean;
  errorCode: number | undefined;
  remains: boolean;
  loadNextChunk: () => void;
  refresh: () => void;
}

const useTipitaka = ({
  type,
  id,
  mode = "read",
  channelId,
  book,
  para,
  active = true,
}: IUseTipitakaProps): IUseTipitakaReturn => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [articleHtml, setArticleHtml] = useState<string[]>([]);
  const [nodeData, setNodeData] = useState<IParagraphProps[]>([]);
  const [toc, setToc] = useState<IChapterToc[]>();
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number>();
  const [remains, setRemains] = useState(false);
  const [refreshCount, setRefreshCount] = useState(0);

  const srcDataMode = mode === "edit" || mode === "wbw" ? "edit" : "read";

  // 用 ref 追踪最新的 articleData，供 loadNextChunk 使用，避免闭包陷阱
  const articleDataRef = useRef<IArticleDataResponse | undefined>(undefined);
  articleDataRef.current = articleData;

  useEffect(() => {
    let ignore = false;

    const load = async () => {
      if (!active || !type || !id) return;

      setLoading(true);

      try {
        let response;
        if (type === "chapter") {
          response = await fetchChapter(id, srcDataMode, channelId);
        } else if (type === "para") {
          const _book = book ?? id;
          response = await fetchPara(_book, para ?? "", srcDataMode, channelId);
        } else {
          return;
        }

        if (ignore) return;

        if (response.ok) {
          setArticleData(response.data);
          setArticleHtml([
            response.data.html ?? response.data.content ?? "<span />",
          ]);
          if (response.data.content && response.data.content_type === "json") {
            setNodeData(JSON.parse(response.data.content));
          }
          setToc(response.data.toc);
          setRemains(response.data.from !== undefined);
          setErrorCode(undefined);
        }
      } catch (e) {
        if (!ignore) {
          setErrorCode(e as number);
        }
      } finally {
        if (!ignore) {
          setLoading(false);
        }
      }
    };

    load();

    return () => {
      ignore = true;
    };
  }, [active, type, id, srcDataMode, book, para, channelId, refreshCount]);

  const loadNextChunk = useCallback(async () => {
    const current = articleDataRef.current;
    if (
      !current ||
      current.paraId === undefined ||
      current.mode === undefined ||
      current.from === undefined ||
      current.to === undefined
    ) {
      setRemains(false);
      return;
    }

    try {
      const response = await fetchNextParaChunk(
        current.paraId,
        current.mode,
        current.from,
        current.to,
        channelId
      );

      if (response.ok && typeof response.data.content === "string") {
        setArticleData((prev) => {
          if (prev) {
            return { ...prev, from: response.data.from };
          }
          return prev;
        });
        setArticleHtml((prev) => [...prev, response.data.content as string]);
        if (response.data.content && response.data.content_type === "json") {
          const newNodes: IParagraphProps[] = JSON.parse(
            response.data.content
          ) as IParagraphProps[];
          setNodeData((prev) => [...prev, ...newNodes]);
        }
      }
    } catch (e) {
      console.error("loadNextChunk error", e);
    }
  }, [channelId]);

  const refresh = useCallback(() => {
    setRefreshCount((c) => c + 1);
  }, []);

  return {
    articleData,
    articleHtml,
    nodeData,
    toc,
    loading,
    errorCode,
    remains,
    loadNextChunk,
    refresh,
  };
};

export default useTipitaka;
