import { List, message, Skeleton } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { get } from "../../request";
import { ICommentListResponse } from "../api/Comment";
import DiscussionCreate from "./DiscussionCreate";

import DiscussionItem, { IComment } from "./DiscussionItem";

interface IWidget {
  topicId?: string;
  focus?: string;
  onItemCountChange?: Function;
}
const DiscussionTopicChildrenWidget = ({
  topicId,
  focus,
  onItemCountChange,
}: IWidget) => {
  const intl = useIntl();
  const [data, setData] = useState<IComment[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (typeof topicId === "undefined") {
      return;
    }
    setLoading(true);

    get<ICommentListResponse>(`/v2/discussion?view=answer&id=${topicId}`)
      .then((json) => {
        console.log(json);
        if (json.ok) {
          console.log("ok", json.data);
          const discussions: IComment[] = json.data.rows.map((item) => {
            return {
              id: item.id,
              resId: item.res_id,
              resType: item.res_type,
              user: item.editor,
              parent: item.parent,
              title: item.title,
              content: item.content,
              createdAt: item.created_at,
              updatedAt: item.updated_at,
            };
          });
          setData(discussions);
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {
        setLoading(false);
      })
      .catch((e) => {
        message.error(e.message);
      });
  }, [intl, topicId]);
  return (
    <div>
      {loading ? (
        <Skeleton title={{ width: 200 }} paragraph={{ rows: 1 }} active />
      ) : (
        <List
          pagination={{
            onChange: (page) => {
              console.log(page);
            },
            pageSize: 10,
          }}
          itemLayout="horizontal"
          dataSource={data}
          renderItem={(item) => {
            console.log("focus", item.id, focus);
            return (
              <List.Item>
                <DiscussionItem
                  data={item}
                  isFocus={item.id === focus ? true : false}
                  onDelete={() => {
                    console.log("delete", item.id, data);
                    if (typeof onItemCountChange !== "undefined") {
                      onItemCountChange(data.length - 1, item.parent);
                    }
                    setData((origin) => {
                      return origin.filter((value) => value.id !== item.id);
                    });
                  }}
                />
              </List.Item>
            );
          }}
        />
      )}
      <DiscussionCreate
        contentType="markdown"
        parent={topicId}
        onCreated={(e: IComment) => {
          console.log("create", e);
          const newData = JSON.parse(JSON.stringify(e));
          setData([...data, newData]);
          if (typeof onItemCountChange !== "undefined") {
            onItemCountChange(data.length + 1, e.parent);
          }
        }}
      />
    </div>
  );
};

export default DiscussionTopicChildrenWidget;
