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
}
const Widget = ({ resId }: IWidget) => {
  const [childrenData, setChildrenData] = useState<IComment[]>(defaultData);
  const [data, setData] = useState<IComment>();

  return (
    <div>
      <CommentTopicHead data={data} />
      <Divider />
      <CommentTopicList data={childrenData} />
      {data ? <CommentItem data={data} create={true} /> : undefined}
    </div>
  );
};

export default Widget;
