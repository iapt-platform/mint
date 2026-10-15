import { useEffect } from "react";
import { Space, Tooltip } from "antd";

import { CommentFillIcon, CommentOutlinedIcon } from "../../assets/icon";
import { useAppSelector } from "../../hooks";
import { badgeCounts, register } from "../../reducers/discussion-badge";
import type { TResType } from "../../api/discussion";
import store from "../../store";
import { openDiscussion } from "./utils";

interface IWidget {
  /** 角标数据到达之前先显示的数（句子数据里自带的 suggestionCount.discussion） */
  initCount?: number;
  resId?: string;
  resType?: TResType;
  hideCount?: boolean;
  hideInZero?: boolean;
  /** 只数我发起的 */
  onlyMe?: boolean;
}

/**
 * 句子 / 逐词上的讨论角标。挂载时登记自己的对象，由 DiscussionBadgeLoader 批量取数
 * （`POST /v3/discussions/batch-count`：未关闭的评论线程数与其中我发起的）。
 *
 * TODO: 课程迁 v3 之后补回 v2 课程模式的「学生的讨论」（老师在答案 channel 上看到学员
 * 在同一个词上的讨论，v2 靠 wbw 坐标匹配）。现在只数这个对象本身上的。
 */
const DiscussionButton = ({
  initCount = 0,
  resId,
  resType = "sentence",
  hideCount = false,
  hideInZero = false,
  onlyMe = false,
}: IWidget) => {
  const counts = useAppSelector(badgeCounts);

  useEffect(() => {
    if (resId && (resType === "sentence" || resType === "wbw")) {
      store.dispatch(register({ type: resType, id: resId }));
    }
  }, [resId, resType]);

  const badge = resId ? counts[`${resType}/${resId}`] : undefined;
  // 角标数据到了就以它为准；没到之前，全部数用句子自带的初始值，「只看我的」先算 0
  const currCount = badge
    ? onlyMe
      ? badge.mine
      : badge.total
    : onlyMe
      ? 0
      : initCount;

  const myCount = (badge?.mine ?? 0) > 0;

  return hideInZero && currCount === 0 ? (
    <></>
  ) : (
    <Tooltip title="讨论">
      <Space
        size={"small"}
        style={{
          cursor: "pointer",
          color: currCount && currCount > 0 ? "#1890ff" : "unset",
        }}
        onClick={() => {
          if (resId) {
            openDiscussion(resId, resType, false);
          }
        }}
      >
        {myCount ? <CommentFillIcon /> : <CommentOutlinedIcon />}
        {hideCount ? <></> : currCount}
      </Space>
    </Tooltip>
  );
};

export default DiscussionButton;
