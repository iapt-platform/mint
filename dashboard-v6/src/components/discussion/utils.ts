import type { TResType } from "../../api/discussion";
import { show, type IShowDiscussion } from "../../reducers/discussion";
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
