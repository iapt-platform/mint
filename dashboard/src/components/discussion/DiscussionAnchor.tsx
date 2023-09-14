import { Skeleton } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import { IArticleResponse } from "../api/Article";
import { ICommentAnchorResponse } from "../api/Comment";
import { ISentenceData, ISentenceResponse } from "../api/Corpus";
import MdView from "../template/MdView";
import AnchorCard from "./AnchorCard";
import { TResType } from "./DiscussionListCard";

export interface IAnchor {
  type: TResType;
  sentence?: ISentenceData;
}

interface IWidget {
  resId?: string;
  resType?: TResType;
  topicId?: string;
  onLoad?: Function;
}
const DiscussionAnchorWidget = ({
  resId,
  resType,
  topicId,
  onLoad,
}: IWidget) => {
  const [content, setContent] = useState<string>();
  const [loading, setLoading] = useState(true);
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
        const url = `/v2/sentence/${resId}`;
        console.log("url", url);
        get<ISentenceResponse>(url)
          .then((json) => {
            if (json.ok) {
              const id = `${json.data.book}-${json.data.paragraph}-${json.data.word_start}-${json.data.word_end}`;
              const channel = json.data.channel.id;
              const url = `/v2/corpus-sent/${id}?mode=edit&channels=${channel}`;
              console.log("url", url);
              get<IArticleResponse>(url).then((json) => {
                if (json.ok) {
                  setContent(json.data.content);
                }
              });
              if (typeof onLoad !== "undefined") {
                onLoad({ type: resType, sentence: json.data });
              }
            }
          })
          .finally(() => setLoading(false));
        break;
      default:
        break;
    }
  }, [resId, resType]);
  return (
    <AnchorCard>
      {loading ? (
        <Skeleton title={{ width: 200 }} paragraph={{ rows: 4 }} active />
      ) : (
        <MdView html={content} />
      )}
    </AnchorCard>
  );
};

export default DiscussionAnchorWidget;
