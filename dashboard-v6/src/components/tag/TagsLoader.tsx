import { useEffect } from "react";

import { useAppSelector } from "../../hooks";
import { sentenceList } from "../../reducers/sentence";
import { tags } from "../../reducers/tag-map";
import type {
  ISentenceTagsRequest,
  ISentenceTagsResponse,
} from "../../api/tag";
import { post } from "../../request";
import store from "../../store";

/** 句子是边渲染边登记的，等登记告一段落再一次性请求 */
const DEBOUNCE_MS = 300;

/**
 * 页面上已渲染句子的标签（逐词上的 TagsArea 从 tag-map reducer 读）。全站只挂一份（layouts/Root.tsx）。
 *
 * FIXME: 仍走 v2 `POST /v2/discussion-count`，只读响应里的 tags（讨论角标已拆到
 * DiscussionBadgeLoader，走 v3）。标签迁 v3 时换掉。
 */
const TagsLoader = () => {
  const sentences = useAppSelector(sentenceList);

  useEffect(() => {
    const ids = sentences.map((item) => item.id).filter(Boolean);
    if (ids.length === 0) {
      return;
    }
    const timer = setTimeout(() => {
      post<ISentenceTagsRequest, ISentenceTagsResponse>(
        "/api/v2/discussion-count",
        { sentences: ids.map((item) => item.split("-")) }
      ).then((json) => {
        if (json.ok) {
          store.dispatch(tags(json.data.tags));
        }
      });
    }, DEBOUNCE_MS);
    return () => clearTimeout(timer);
  }, [sentences]);

  return <></>;
};

export default TagsLoader;
