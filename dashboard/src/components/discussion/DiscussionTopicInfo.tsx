import { message } from "antd";
import { useEffect, useState } from "react";

import { get } from "../../request";
import { ICommentResponse } from "../api/Comment";
import DiscussionItem, { IComment } from "./DiscussionItem";
import { TDiscussionType } from "./Discussion";

interface IWidget {
  topicId?: string;
  topic?: IComment;
  childrenCount?: number;
  hideTitle?: boolean;
  onDelete?: Function;
  onReply?: Function;
  onClose?: Function;
  onReady?: Function;
  onConvert?: Function;
}
const DiscussionTopicInfoWidget = ({
  topicId,
  topic,
  childrenCount,
  hideTitle = false,
  onReady,
  onDelete,
  onReply,
  onClose,
  onConvert,
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
    console.info("discussion api request", url);
    get<ICommentResponse>(url)
      .then((json) => {
        console.debug("api response", json);
        if (json.ok) {
          const item = json.data;
          const discussion: IComment = {
            id: item.id,
            resId: item.res_id,
            resType: item.res_type,
            type: item.type,
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
            console.log("discussion on ready");
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
          hideTitle={hideTitle}
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
          onConvert={(value: TDiscussionType) => {
            if (typeof onConvert !== "undefined") {
              onConvert(value);
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
