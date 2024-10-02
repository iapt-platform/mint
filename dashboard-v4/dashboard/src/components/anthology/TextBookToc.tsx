import { useEffect, useState } from "react";
import AnthologyTocTree from "./AnthologyTocTree";
import { get } from "../../request";
import { ICourseResponse } from "../api/Course";

interface IWidget {
  courseId?: string | null;
  channels?: string[];
  onSelect?: Function;
  onClick?: Function;
  onArticleSelect?: Function;
}
const TextBookTocWidget = ({
  courseId,
  channels,
  onSelect,
  onClick,
  onArticleSelect,
}: IWidget) => {
  const [anthologyId, setAnthologyId] = useState<string>();

  useEffect(() => {
    if (!courseId) {
      return;
    }
    const url = `/v2/course/${courseId}`;
    console.debug("course url", url);
    get<ICourseResponse>(url).then((json) => {
      console.debug("course data", json.data);
      if (json.ok) {
        setAnthologyId(json.data.anthology_id);
      }
    });
  }, [courseId]);

  return (
    <AnthologyTocTree
      anthologyId={anthologyId}
      channels={channels}
      onClick={(anthology: string, article: string, target: string) => {
        console.debug("AnthologyTocTree onClick", article);
        if (typeof onClick !== "undefined") {
          onClick(article, target);
        }
      }}
    />
  );
};

export default TextBookTocWidget;
