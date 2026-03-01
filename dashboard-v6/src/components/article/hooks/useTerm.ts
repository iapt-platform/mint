import { useEffect, useState, useTransition } from "react";

import type { ArticleMode, IArticleDataResponse } from "../../../api/Article";
import { getTerm, type ITermDataResponse } from "../../../api/Term";
import { message } from "antd";

interface IUseTermOptions {
  id?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
}

export function useTerm({ id, mode = "read", channelId }: IUseTermOptions) {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [term, setTerm] = useState<ITermDataResponse>();
  const [errorCode, setErrorCode] = useState<number>();
  const [isPending, startTransition] = useTransition();

  useEffect(() => {
    if (typeof id === "undefined") return;

    const queryMode = mode === "edit" || mode === "wbw" ? "edit" : "read";

    startTransition(async () => {
      try {
        const json = await getTerm({
          id: id,
          mode: queryMode,
          channelsId: channelId,
        });

        if (!json.ok) {
          message.error(json.message);
          return;
        }

        const { data } = json;

        setArticleData({
          uid: data.guid,
          title: data.meaning,
          subtitle: data.word,
          summary: data.note,
          content: data.note ?? "",
          content_type: "markdown",
          html: data.html ?? data.note ?? "<span />",
          editor: data.editor,
          status: 30,
          lang: data.language,
          created_at: data.created_at,
          updated_at: data.updated_at,
        });

        setTerm(data);
      } catch (e) {
        setErrorCode(e as number);
      }
    });
  }, [id, channelId, mode]);

  return { articleData, term, errorCode, loading: isPending };
}
