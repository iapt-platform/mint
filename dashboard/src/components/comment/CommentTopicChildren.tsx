import { List, message } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { get } from "../../request";
import { ICommentListResponse } from "../api/Comment";
import CommentCreate from "./CommentCreate";

import CommentItem, { IComment } from "./CommentItem";

interface IWidget {
  topicId?: string;
  onItemCountChange?: Function;
}
const Widget = ({ topicId, onItemCountChange }: IWidget) => {
  const intl = useIntl();
  const [data, setData] = useState<IComment[]>();
  useEffect(() => {
    if (typeof topicId === "undefined") {
      return;
    }
    get<ICommentListResponse>(`/v2/discussion?view=answer&id=${topicId}`)
      .then((json) => {
        console.log(json);
        if (json.ok) {
          console.log(intl.formatMessage({ id: "flashes.success" }));
          const discussions: IComment[] = json.data.rows.map((item) => {
            return {
              id: item.id,
              resId: item.res_id,
              resType: item.res_type,
              user: item.editor,
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
      .catch((e) => {
        message.error(e.message);
      });
  }, [topicId]);
  return (
    <div>
      <List
        pagination={{
          onChange: (page) => {
            console.log(page);
          },
          pageSize: 10,
        }}
        itemLayout="horizontal"
        dataSource={data}
        renderItem={(item) => (
          <List.Item>
            <CommentItem data={item} />
          </List.Item>
        )}
      />
      <CommentCreate
        parent={topicId}
        onCreated={(e: IComment) => {
          console.log("create", e);
          const newData = JSON.parse(JSON.stringify(e));
          let count = 0;
          if (typeof data === "undefined") {
            count = 1;
            setData([newData]);
          } else {
            count = data.length + 1;
            setData([...data, newData]);
          }
          if (typeof onItemCountChange !== "undefined") {
            onItemCountChange(count, e.parent);
          }
        }}
      />
    </div>
  );
};

export default Widget;
