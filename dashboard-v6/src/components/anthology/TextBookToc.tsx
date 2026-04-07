import { useEffect, useState } from "react";
import AnthologyTocTree from "./AnthologyTocTree";
import { get } from "../../request";
import type { ICourseResponse } from "../../api/course";
import type { TTarget } from "../../types";

interface IWidget {
  courseId?: string | null;
  channels?: string[];
  onClick?: (article: string, target?: TTarget) => void;
}
const TextBookTocWidget = ({ courseId, channels, onClick }: IWidget) => {
  const [anthologyId, setAnthologyId] = useState<string>();

  useEffect(() => {
    if (!courseId) {
      return;
    }
    const url = `/api/v2/course/${courseId}`;
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
      onClick={(_anthology, article, target) => {
        console.debug("AnthologyTocTree onClick", article);
        if (typeof onClick !== "undefined") {
          onClick(article, target);
        }
      }}
    />
  );
};

export default TextBookTocWidget;
