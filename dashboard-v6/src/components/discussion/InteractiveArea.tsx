import { useEffect, useState } from "react";
import { Tabs } from "antd";

import Discussion from "./Discussion";
import QaBox from "./QaBox";
import {
  asDiscussionTarget,
  fetchTopicSummary,
  type TDiscussionType,
  type TResType,
} from "../../api/discussion";

interface IWidget {
  resId?: string;
  resType?: TResType;
}

/** 文章底部的三个页签，按这个顺序排 */
const TABS: { type: TDiscussionType; label: string }[] = [
  { type: "qa", label: "问答" },
  { type: "help", label: "求助" },
  { type: "discussion", label: "讨论" },
];

/**
 * 文章底部的互动区：问答 / 求助 / 讨论三个页签。
 *
 * 某个页签出现的条件：当前用户能在这里发起这种话题，或者已经有这种话题。
 * 取代 v2 `GET /v2/interactive/{id}` 的 can_create / can_reply / count，改用每种类型
 * 各一次 `GET /v3/discussions/summary`（creatable_types + status_counts）。
 */
const InteractiveAreaWidget = ({ resId, resType }: IWidget) => {
  // 记下是哪个对象的结果，换文章时旧结果自然失效，不必在 effect 里先清状态
  const [visible, setVisible] = useState<{
    key: string;
    types: TDiscussionType[];
  }>();
  const target = asDiscussionTarget(resType);
  const key = `${target}/${resId}`;

  useEffect(() => {
    if (!resId || !target) {
      return;
    }
    Promise.all(
      TABS.map(({ type }) =>
        fetchTopicSummary({ target_type: target, target_id: resId, type })
      )
    ).then((summaries) => {
      const types = TABS.filter(({ type }, i) => {
        const counts = summaries[i].status_counts;
        const total = (counts?.active ?? 0) + (counts?.close ?? 0);
        return summaries[i].creatable_types?.includes(type) || total > 0;
      }).map(({ type }) => type);
      setVisible({ key: `${target}/${resId}`, types });
    });
  }, [resId, target]);

  const types = visible?.key === key ? visible.types : [];
  if (types.length === 0) {
    return <></>;
  }

  return (
    <Tabs
      size="small"
      items={TABS.filter(({ type }) => types.includes(type)).map(
        ({ type, label }) => ({
          label,
          key: type,
          children:
            type === "qa" ? (
              <QaBox resId={resId} resType={resType} />
            ) : (
              <Discussion resId={resId} resType={resType} type={type} />
            ),
        })
      )}
    />
  );
};

export default InteractiveAreaWidget;
