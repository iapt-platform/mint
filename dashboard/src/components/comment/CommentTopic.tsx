import { useState } from "react";
import { Divider } from "antd";

import CommentItem, { IComment } from "./CommentItem";
import CommentTopicList from "./CommentTopicList";
import CommentTopicHead from "./CommentTopicHead";

interface IWidget {
  resId: string;
}
const Widget = ({ resId }: IWidget) => {
  const [childrenData, setChildrenData] = useState<IComment[]>([]);
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
