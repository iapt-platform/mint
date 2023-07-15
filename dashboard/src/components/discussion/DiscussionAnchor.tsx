import { useEffect, useState } from "react";
import { get } from "../../request";
import { ICommentAnchorResponse } from "../api/Comment";
import { ISentenceResponse } from "../api/Corpus";
import MdView from "../template/MdView";
import AnchorCard from "./AnchorCard";
import { TResType } from "./DiscussionListCard";

interface IWidget {
  resId?: string;
  resType?: TResType;
  topicId?: string;
}
const DiscussionAnchorWidget = ({ resId, resType, topicId }: IWidget) => {
  const [content, setContent] = useState<string>();
  useEffect(() => {
    if (typeof topicId === "string") {
      get<ICommentAnchorResponse>(`/v2/discussion-anchor/${topicId}`).then(
        (json) => {
          console.log(json);
          if (json.ok) {
            setContent(json.data);
          }
        }
      );
    }
  }, [topicId]);

  useEffect(() => {
    switch (resType) {
      case "sentence":
        get<ISentenceResponse>(`/v2/sentence/${resId}`).then((json) => {
          if (json.ok) {
            setContent(json.data.html);
          }
        });
        break;

      default:
        break;
    }
  }, [resId, resType]);
  return (
    <AnchorCard>
      <MdView html={content} />
    </AnchorCard>
  );
};

export default DiscussionAnchorWidget;
