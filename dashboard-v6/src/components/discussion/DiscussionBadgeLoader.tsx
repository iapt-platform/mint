import { useEffect, useRef } from "react";

import { useAppSelector } from "../../hooks";
import { badgeTargets, setCounts } from "../../reducers/discussion-badge";
import {
  fetchDiscussionBadges,
  type TDiscussionBadgeTarget,
} from "../../api/discussion";
import store from "../../store";

/** 角标是边渲染边登记的，等登记告一段落再一次性请求 */
const DEBOUNCE_MS = 300;
/** 与后端 BatchCountDiscussionRequest::MAX_TARGETS 一致 */
const BATCH_SIZE = 500;

/**
 * 讨论角标的数据源：把各角标登记的对象（句子 / 逐词）攒一批，调
 * `POST /v3/discussions/batch-count`。每个对象只取一次；写入讨论后的刷新走
 * utils.refreshDiscussionBadge。全站只挂一份（layouts/Root.tsx）。
 */
const DiscussionBadgeLoader = () => {
  const targets = useAppSelector(badgeTargets);
  // 已经发出过请求的对象，不重复取
  const requested = useRef(new Set<string>());

  useEffect(() => {
    const pending = targets.filter((key) => !requested.current.has(key));
    if (pending.length === 0) {
      return;
    }
    const timer = setTimeout(() => {
      pending.forEach((key) => requested.current.add(key));
      const items = pending.map((key) => {
        const [type, id] = key.split("/");
        return { type, id } as TDiscussionBadgeTarget;
      });
      for (let i = 0; i < items.length; i += BATCH_SIZE) {
        fetchDiscussionBadges(items.slice(i, i + BATCH_SIZE)).then((list) =>
          store.dispatch(setCounts(list))
        );
      }
    }, DEBOUNCE_MS);
    return () => clearTimeout(timer);
  }, [targets]);

  return <></>;
};

export default DiscussionBadgeLoader;
