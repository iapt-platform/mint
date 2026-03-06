import type {
  ICommentApiData,
  IDiscussionCountResponse,
} from "../../api/Comment";
import type { IComment, TResType } from "../../api/discussion";
import { show, type IShowDiscussion } from "../../reducers/discussion";
import { upgrade } from "../../reducers/discussion-count";
import { openPanel } from "../../reducers/right-panel";
import { get } from "../../request";
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

export const discussionCountUpgrade = (resId?: string) => {
  if (typeof resId === "undefined") {
    return;
  }
  const url = `/v2/discussion-count/${resId}`;
  console.info("discussion-count api request", url);
  get<IDiscussionCountResponse>(url).then((json) => {
    console.debug("discussion-count api response", json);
    if (json.ok) {
      store.dispatch(upgrade({ resId: resId, data: json.data.discussions }));
    } else {
      console.error(json.message);
    }
  });
};

export const toIComment = (value: ICommentApiData): IComment => {
  return {
    id: value.id,
    resId: value.res_id,
    resType: value.res_type,
    type: value.type,
    user: value.editor,
    title: value.title,
    parent: value.parent,
    tplId: value.tpl_id,
    content: value.content,
    createdAt: value.created_at,
    updatedAt: value.updated_at,
  };
};
