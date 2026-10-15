import { fetchDiscussionBadges, type TResType } from "../../api/discussion";
import { show, type IShowDiscussion } from "../../reducers/discussion";
import { setCounts } from "../../reducers/discussion-badge";
import { openPanel } from "../../reducers/right-panel";
import store from "../../store";

export const openDiscussion = (
  resId: string,
  resType: TResType,
  withStudent: boolean
) => {
  const data: IShowDiscussion = {
    type: "discussion",
    resId: resId,
    resType: resType,
    withStudent: withStudent,
  };
  console.debug("discussion show", data);
  store.dispatch(show(data));
  store.dispatch(openPanel("discussion"));
};

/**
 * 写入讨论之后重取某个对象的讨论角标（只有句子 / 逐词有角标，其它对象直接返回）。
 */
export const refreshDiscussionBadge = (resType?: TResType, resId?: string) => {
  if (!resId || (resType !== "sentence" && resType !== "wbw")) {
    return;
  }
  fetchDiscussionBadges([{ type: resType, id: resId }]).then((list) =>
    store.dispatch(setCounts(list))
  );
};
