import { useEffect } from "react";
import { useAppSelector } from "../../hooks";
import { discussions, tags } from "../../reducers/discussion-count";
import { sentenceList } from "../../reducers/sentence";
import type {
  IDiscussionCountRequest,
  IDiscussionCountResponse,
} from "../../api/Comment";
import { post } from "../../request";
import store from "../../store";

interface IWidget {
  courseId?: string | null;
}

const DiscussionCount = ({ courseId }: IWidget) => {
  const sentences = useAppSelector(sentenceList);

  console.debug("sentences", sentences);

  useEffect(() => {
    const sentId: string[] = sentences
      .filter((value) => typeof value.id != "undefined")
      .map((item) => item.id);
    if (sentId.length === 0) {
      return;
    }
    const url = "/v2/discussion-count";
    const data: IDiscussionCountRequest = {
      course_id: courseId ?? undefined,
      sentences: sentId.map((item) => item.split("-")),
    };
    console.info("discussion-count api request", url, data);
    post<IDiscussionCountRequest, IDiscussionCountResponse>(url, data).then(
      (json) => {
        console.debug("discussion-count api response", json);
        if (json.ok) {
          store.dispatch(discussions(json.data.discussions));
          store.dispatch(tags(json.data.tags));
        }
      }
    );
  }, [courseId, sentences]);
  return <></>;
};

export default DiscussionCount;
