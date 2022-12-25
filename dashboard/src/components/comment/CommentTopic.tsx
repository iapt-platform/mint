import { useEffect, useState } from "react";
import { Divider, message } from "antd";

import CommentItem, { IComment } from "./CommentItem";
import CommentTopicList from "./CommentTopicList";
import CommentTopicHead from "./CommentTopicHead";
import { get } from "../../request";
import { ICommentListResponse } from "../api/Comment";
import { useIntl } from "react-intl";
import CommentCreate from "./CommentCreate";

const defaultData: IComment[] = Array(5)
  .fill(3)
  .map((item, id) => {
    return {
      id: "dd",
      content: "评论内容",
      title: "评论标题" + id,
      user: {
        id: "string",
        nickName: "Visuddhinanda",
        realName: "Visuddhinanda",
        avatar: "",
      },
    };
  });

interface IWidget {
  resId: string;
  resType: string;
  comment?: IComment;
  onItemCountChange?: Function;
}
const Widget = ({ resId, resType, comment, onItemCountChange }: IWidget) => {
  const intl = useIntl();
  const [childrenData, setChildrenData] = useState<IComment[]>(defaultData);
  useEffect(() => {
    get<ICommentListResponse>(`/v2/discussion?view=answer&id=${comment?.id}`)
      .then((json) => {
        console.log(json);
        if (json.ok) {
          console.log(intl.formatMessage({ id: "flashes.success" }));
          const discussions: IComment[] = json.data.rows.map((item) => {
            return {
              id: item.id,
              resId: item.res_id,
              resType: item.res_type,
              user: {
                id: item.editor?.id ? item.editor.id : "null",
                nickName: item.editor?.nickName ? item.editor.nickName : "null",
                realName: item.editor?.userName ? item.editor.userName : "null",
                avatar: item.editor?.avatar ? item.editor.avatar : "null",
              },
              title: item.title,
              content: item.content,
              createdAt: item.created_at,
              updatedAt: item.updated_at,
            };
          });
          setChildrenData(discussions);
        } else {
          message.error(json.message);
        }
      })
      .catch((e) => {
        message.error(e.message);
      });
  }, [comment]);
  return (
    <div>
      <CommentTopicHead data={comment} />
      <Divider />
      <CommentTopicList data={childrenData} />
      <CommentCreate
        resId={resId}
        resType={resType}
        parent={comment?.id}
        onCreated={(e: IComment) => {
          console.log("create", e);
          const newData = JSON.parse(JSON.stringify(e));
          if (typeof onItemCountChange !== "undefined") {
            onItemCountChange(childrenData.length + 1, e.parent);
          }
          setChildrenData([...childrenData, newData]);
        }}
      />
    </div>
  );
};

export default Widget;
