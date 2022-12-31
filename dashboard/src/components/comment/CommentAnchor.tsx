import { useEffect, useState } from "react";
import { get } from "../../request";
import { ICommentAnchorResponse } from "../api/Comment";
import MdView from "../template/MdView";

interface IWidget {
  id?: string;
}
const Widget = ({ id }: IWidget) => {
  const [content, setContent] = useState<string>();
  useEffect(() => {
    if (typeof id === "string") {
      get<ICommentAnchorResponse>(`/v2/discussion-anchor/${id}`).then(
        (json) => {
          console.log(json);
          if (json.ok) {
            setContent(json.data);
          }
        }
      );
    }
  }, [id]);
  return (
    <div>
      <MdView html={content} />
    </div>
  );
};

export default Widget;
