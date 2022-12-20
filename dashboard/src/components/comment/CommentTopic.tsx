import { useState } from "react";
import { Divider } from "antd";

import CommentItem, { IComment } from "./CommentItem";
import CommentTopicList from "./CommentTopicList";
import CommentTopicHead from "./CommentTopicHead";

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
  comment?: IComment;
}
const Widget = ({ resId, comment }: IWidget) => {
  const [childrenData, setChildrenData] = useState<IComment[]>(defaultData);

  return (
    <div>
      <CommentTopicHead data={comment} />
      <Divider />
      <CommentTopicList data={childrenData} />
      {comment ? <CommentItem data={comment} create={true} /> : undefined}
    </div>
  );
};

export default Widget;
