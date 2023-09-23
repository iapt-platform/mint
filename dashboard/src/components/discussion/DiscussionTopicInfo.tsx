import { message } from "antd";
import { useEffect, useState } from "react";

import { get } from "../../request";
import { ICommentResponse } from "../api/Comment";
import DiscussionItem, { IComment } from "./DiscussionItem";

interface IWidget {
  topicId?: string;
  topic?: IComment;
  childrenCount?: number;
  onDelete?: Function;
  onReply?: Function;
  onClose?: Function;
  onReady?: Function;
}
const DiscussionTopicInfoWidget = ({
  topicId,
  topic,
  childrenCount,
  onReady,
  onDelete,
  onReply,
  onClose,
}: IWidget) => {
  const [data, setData] = useState<IComment | undefined>(topic);
  useEffect(() => {
    setData(topic);
  }, [topic]);
  useEffect(() => {
    setData((origin) => {
      if (typeof origin !== "undefined") {
        origin.childrenCount = childrenCount;
        return origin;
      }
    });
  }, [childrenCount]);

  useEffect(() => {
    if (typeof topicId === "undefined") {
      return;
    }
    const url = `/v2/discussion/${topicId}`;
    console.log("url", url);
    get<ICommentResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log("flashes.success");
          const item = json.data;
          const discussion: IComment = {
            id: item.id,
            resId: item.res_id,
            resType: item.res_type,
            parent: item.parent,
            user: item.editor,
            title: item.title,
            content: item.content,
            html: item.html,
            status: item.status,
            childrenCount: item.children_count,
            createdAt: item.created_at,
            updatedAt: item.updated_at,
          };
          setData(discussion);
          if (typeof onReady !== "undefined") {
            console.log("on ready");
            onReady(discussion);
          }
        } else {
          message.error(json.message);
        }
      })
      .catch((e) => {
        message.error(e.message);
      });
  }, [topicId]);

  return (
    <div>
      {data ? (
        <DiscussionItem
          data={data}
          onDelete={() => {
            if (typeof onDelete !== "undefined") {
              onDelete(data.id);
            }
          }}
          onReply={() => {
            if (typeof onReply !== "undefined") {
              onReply(data);
            }
          }}
          onClose={() => {
            if (typeof onClose !== "undefined") {
              onClose(data);
            }
          }}
        />
      ) : (
        <></>
      )}
    </div>
  );
};

export default DiscussionTopicInfoWidget;
