import { Skeleton } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import type { IArticleResponse } from "../../api/article";

import AnchorCard from "./AnchorCard";

import { Link } from "react-router";
import type { TResType } from "../../api/discussion";
import type { ISentenceData, ISentenceResponse } from "../../api/sentence";
import MdView from "../general/MdView";
import { articlePath } from "../../utils";

export interface IAnchor {
  type: TResType;
  sentence?: ISentenceData;
}

interface IWidget {
  resId?: string;
  resType?: TResType;
  onLoad?: Function;
}
const DiscussionAnchorWidget = ({ resId, resType, onLoad }: IWidget) => {
  const [title, setTitle] = useState<React.ReactNode>();
  const [content, setContent] = useState<string>();
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    let url: string;
    switch (resType) {
      case "sentence":
        url = `/api/v2/sentence/${resId}`;
        console.info("api request", url);
        setLoading(true);
        get<ISentenceResponse>(url)
          .then((json) => {
            console.info("api response", json);
            if (json.ok) {
              const id = `${json.data.book}-${json.data.paragraph}-${json.data.word_start}-${json.data.word_end}`;
              const channel = json.data.channel.id;
              const url = `/api/v2/corpus-sent/${id}?mode=edit&channels=${channel}`;
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
      case "article":
        url = `/api/v2/article/${resId}`;
        console.info("url", url);
        setLoading(true);

        get<IArticleResponse>(url)
          .then((json) => {
            if (json.ok) {
              setTitle(
                <Link to={articlePath("article", resId ?? "")}>{json.data.title}</Link>
              );
              setContent(json.data.content?.substring(0, 200));
            }
          })
          .finally(() => setLoading(false));
        break;
      default:
        break;
    }
  }, [resId, resType]);

  return (
    <AnchorCard title={title}>
      {loading ? (
        <Skeleton title={{ width: 200 }} paragraph={{ rows: 4 }} active />
      ) : (
        <div>
          <MdView html={content} />
        </div>
      )}
    </AnchorCard>
  );
};

export default DiscussionAnchorWidget;
