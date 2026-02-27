import { useEffect, useState, useTransition } from "react";

import type { ArticleMode, IArticleDataResponse } from "../../../api/Article";
import { getTerm } from "../../../api/Term";
import { message } from "antd";

interface IUseTermOptions {
  id?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
}

export function useTerm({ id, mode = "read", channelId }: IUseTermOptions) {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [articleHtml, setArticleHtml] = useState<string[]>(["<span />"]);
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
          html: data.html,
          path: [],
          editor: data.editor,
          status: 30,
          lang: data.language,
          created_at: data.created_at,
          updated_at: data.updated_at,
        });

        setArticleHtml(
          data.html ? [data.html] : data.note ? [data.note] : ["<span />"]
        );
      } catch (e) {
        setErrorCode(e as number);
      }
    });
  }, [id, channelId, mode]);

  return { articleData, articleHtml, errorCode, loading: isPending };
}
